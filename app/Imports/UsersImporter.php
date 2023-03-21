<?php

namespace App\Imports;

use App\Imports\Exceptions\InvalidDataException;
use App\Mail\Mailer;
use App\Models\AccessLog;
use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Import;
use App\Models\Tag;
use App\Models\User;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserDelete;
use App\Services\AccessLogMeta\AccessLogUserSignup;
use App\Services\AccessLogMeta\AccessLogUserUpdate;
use App\Services\UserAnonymisation;
use DB;
use Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UsersImporter extends Importer
{
    protected $necessaryHeaders = [
        'firstname',
    ];
    /**
     * @var AccessLogEngine
     */
    private $accessLogEngine;
    /**
     * @var Mailer
     */
    private $mailer;
    private $invitationEmails;
    private $newQuizTeamUserIds;

    /**
     * @var array An array with additional data we need for the import:
     *  [
     *    'tags' => $tags, // Array of tag models or null
     *    'quizTeam' => $quizTeam, // A quiz team model or null
     *    'appId' => appId(), // The id of the app
     *    'creatorId' => Auth::user()->id, // The user id of the creator
     *    'compareHeader' => $compareHeader, // The header by which we want to compare the users
     *  ]
     */
    private $additionalData;
    /**
     * @var array An array of header associations so we know which column in the array contains which data. Example:
     *  [
     *    null,
     *    'firstname',
     *    null,
     *    'mail'
     *  ]
     */
    private $headers;
    /**
     * @var array The array of users
     */
    private $users;
    private $app;
    private $creator;
    private $uniqueMetaFields;

    public function __construct(AccessLogEngine $accessLogEngine, Mailer $mailer)
    {
        $this->accessLogEngine = $accessLogEngine;
        $this->mailer = $mailer;
    }

    /**
     * @param $additionalData
     * @param $headers
     * @param $users
     * @throws \Exception
     */
    protected function importData($additionalData, $headers, $users)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->users = $users;
        $this->app = App::find($this->additionalData['appId']);
        $this->invitationEmails = [];
        $this->newQuizTeamUserIds = [];
        $this->import = Import::findOrFail($this->additionalData['importId']);
        $this->creator = User::findOrFail($this->additionalData['creatorId']);
        $this->uniqueMetaFields = $this->app->getUniqueMetaFields();

        $tagChanges = $this->collectTagChanges();
        if (count($tagChanges['invalidTags']) > 0) {
            app()->abort(400, 'Ungültige TAGs angegeben');
        }

        $this->stepDone();

        DB::transaction(function () use ($tagChanges) {
            // Import the new tags
            $this->importNewTags($tagChanges);
            $this->stepDone();

            $userChanges = $this->collectUserChanges();
            $this->stepDone();

            $this->importNewUsers($userChanges);
            $this->stepDone();

            $this->updateExistingUsers($userChanges);
            $this->stepDone();

            if ($this->additionalData['deleteUsers']) {
                $this->deleteUsers($userChanges);
                $this->stepDone();
            }
        });

        $this->sendEmails();

        $this->importDone();
    }

    /**
     * Imports new tags.
     *
     * @param $tagChanges
     */
    private function importNewTags($tagChanges)
    {
        if ($tagChanges['newTags']) {
            foreach ($tagChanges['newTags'] as $newTag) {
                $tag = new Tag();
                $tag->app_id = $this->app->id;
                $tag->creator_id = $this->additionalData['creatorId'];
                $tag->label = $newTag['label'];
                $tag->tag_group_id = $newTag['tag_group_id'];
                $tag->save();
            }
            // Re-fetch the app model, because the app's tags have changed
            $this->app = App::find($this->additionalData['appId']);
        }
    }

    /**
     * Creates new users.
     *
     * @param $userChanges
     * @throws InvalidDataException
     */
    private function importNewUsers($userChanges)
    {
        $userChangeCount = count($userChanges['newUsers']);
        $idx = 0;
        foreach ($userChanges['newUsers'] as $newUser) {
            $newUserData = $newUser['userData'];
            // Create the new user
            $password = randomPassword();
            $user = new User();
            $user->app_id = $this->app->id;
            $user->tos_accepted = 0;
            $user->active = 1;
            $user->password = Hash::make($password);
            $user->is_admin = false;
            $user->email = createDummyMail();
            $user->save();

            $user = $this->updateUserData($user, $newUserData, true);

            $this->accessLogEngine->log(AccessLog::ACTION_USER_SIGNUP, new AccessLogUserSignup($user), $this->additionalData['creatorId']);

            // Save the invitation email
            if (!isDummyMail($user->email)) {
                $this->invitationEmails[] = [
                    'appId' => $user->app_id,
                    'email' => $user->email,
                    'userId' => $user->id,
                    'password' => $password,
                ];
            }

            AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

            $this->setStepProgress($idx++ / $userChangeCount);
        }
    }

    /**
     * Updates existing users.
     *
     * @param $userChanges
     *
     * @throws InvalidDataException
     */
    private function updateExistingUsers($userChanges)
    {
        $userUpdateCount = count($userChanges['updateUsers']);
        $idx = 0;
        foreach ($userChanges['updateUsers'] as $updateUser) {
            $userData = $updateUser['userData'];
            $existingUser = $updateUser['existingUser'];

            if($this->creatorHasPermission($existingUser)) {
                $this->updateUserData($existingUser, $userData, false);
            }

            $this->setStepProgress($idx++ / $userUpdateCount);
        }
    }

    /**
     * Deletes users which don't exist in the uploaded csv.
     *
     * @param $userChanges
     * @throws \Exception
     */
    private function deleteUsers($userChanges)
    {
        if (! $this->additionalData['deleteUsers']) {
            throw new \Exception('Tried to delete users even though we don\'t want to delete users');
        }
        /** @var UserAnonymisation $ua */
        $ua = app(UserAnonymisation::class);
        $idx = 0;
        $userDeletionCount = count($userChanges['deleteUsers']);
        foreach ($userChanges['deleteUsers'] as $deleteUser) {
            if($this->creatorHasPermission($deleteUser['existingUser'])) {
                if ($ua->anonymiseUser($deleteUser['existingUser'])) {
                    $this->accessLogEngine->log(AccessLog::ACTION_USER_DELETE, new AccessLogUserDelete($deleteUser['existingUser']), $this->additionalData['creatorId']);
                }
            }
            $this->setStepProgress($idx++ / $userDeletionCount);
        }
    }

    /**
     * Executes all necessary updates for a user.
     *
     * @param $existingUser
     * @param $newUserData
     * @param bool $isNewUser
     *
     * @return User
     * @throws InvalidDataException
     */
    private function updateUserData(User $existingUser, $newUserData, $isNewUser = false)
    {
        $this->updateBasicUserData($existingUser, $newUserData, $isNewUser);

        $tagUpdates = $this->updateUserTags($existingUser, $newUserData);

        $this->updateUserQuizTeams($existingUser, $isNewUser);

        $this->updateUserForcePasswordReset($existingUser, $isNewUser);

        if (! $isNewUser) {
            $this->accessLogEngine->log(AccessLog::ACTION_USER_UPDATE, new AccessLogUserUpdate($existingUser, $tagUpdates), $this->additionalData['creatorId']);
        }

        $existingUser->save();

        return $existingUser;
    }

    /**
     * Updates the basic user table fields.
     *
     * @param $existingUser
     * @param $newUserData
     * @param $isNewUser
     *
     * @throws InvalidDataException
     */
    private function updateBasicUserData($existingUser, $newUserData, $isNewUser)
    {
        $name = $this->getUsername($newUserData);
        $language = $this->app->getLanguage();
        if ($this->hasData($this->headers, 'language') && $userLanguage = $this->getDataPoint($newUserData, $this->headers, 'language')) {
            $language = $userLanguage;
        }

        if ($this->hasData($this->headers, 'mail') && ($isNewUser || $this->additionalData['compareHeader'] !== 'mail')) {
            // We only want to update the user's email when the email isn't selected as compare header or when it's a new user
            $existingUser->email = strtolower($this->getDataPoint($newUserData, $this->headers, 'mail'));
        }
        $existingUser->username = $name;
        $existingUser->language = $language;

        if ($this->hasData($this->headers, 'firstname')) {
            $existingUser->firstname = $this->getDataPoint($newUserData, $this->headers, 'firstname');
        }
        if ($this->hasData($this->headers, 'lastname')) {
            $existingUser->lastname = $this->getDataPoint($newUserData, $this->headers, 'lastname');
        }

        // Update the metadata
        $metaUpdateKeys = $this->getUpdatedMetaKeys($existingUser, $newUserData);
        if ($metaUpdateKeys) {
            $meta = $existingUser->getMeta();
            foreach ($metaUpdateKeys as $metaUpdateKey) {
                $dbKey = substr($metaUpdateKey, strlen('meta_'));
                $meta[$dbKey] = $this->getDataPoint($newUserData, $this->headers, $metaUpdateKey);
            }
            $existingUser->setMeta($meta);
        }
    }

    /**
     * Updates the user's tags and returns an array of tag updates.
     *
     * @param $existingUser
     * @param $newUserData
     *
     * @return array|null
     * @throws InvalidDataException
     */
    private function updateUserTags($existingUser, $newUserData)
    {
        $newTags = $this->getNewTagIds($existingUser, $newUserData);
        $tagUpdates = null;
        if ($newTags !== null) {
            $tagUpdates = $existingUser->tags()->sync($newTags);
        }

        return $tagUpdates;
    }

    /**
     * If a quiz team was set for this import, add this user to it.
     *
     * @param User $existingUser
     * @param $isNewUser
     */
    private function updateUserQuizTeams(User $existingUser, $isNewUser)
    {
        $quizTeam = $this->additionalData['quizTeam'];
        if (! $quizTeam || $existingUser->quizTeams->contains($quizTeam)) {
            return;
        }
        // Add the new quiz teams (without removing old ones)
        $existingUser->quizTeams()->syncWithoutDetaching([$quizTeam->id]);
        if (! $isNewUser && $existingUser->email) {
            $this->newQuizTeamUserIds[] = $existingUser->id;
        }
    }

    /**
     * Sets force password reset for the new user.
     *
     * @param User $existingUser
     * @param $isNewUser
     */
    private function updateUserForcePasswordReset(User $existingUser, $isNewUser)
    {
        if(!$isNewUser) {
            return;
        }
        $existingUser->load('tags');
        $appProfile = $existingUser->getAppProfile();
        if ($appProfile->getValue('signup_force_password_reset', false, true)) {
            $existingUser->force_password_reset = true;
        }
    }

    /**
     * Collects information about what is going to be created or updated.
     *
     * @param $additionalData
     * @param $headers
     * @param $users
     *
     * @return array
     * @throws InvalidDataException
     */
    public function collectChanges($additionalData, $headers, $users)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->users = $users;
        $this->app = App::find($this->additionalData['appId']);
        $this->creator = User::findOrFail($this->additionalData['creatorId']);
        $data = [
            'actions' => [],
            'errors' => [],
        ];
        $userData = $this->collectUserChanges();
        $convertToUsername = function ($user) {
            return $this->getDataPoint($user['userData'], $this->headers, $this->additionalData['compareHeader']);
        };
        $newGlobalTags = $additionalData['tags'];
        if (! $newGlobalTags) {
            if (count($userData['newUsers']) === 1) {
                $newUsersMessage = '1 Benutzer wird neu angelegt';
            } else {
                $newUsersMessage = count($userData['newUsers']).' Benutzer werden neu angelegt';
            }
        } else {
            $tagNames = $newGlobalTags->pluck('label')->implode(', ');
            if (count($userData['newUsers']) === 1) {
                $newUsersMessage = '1 Benutzer wird mit '.(count($newGlobalTags) === 1 ? 'dem Tag ' : 'den TAGs ').$tagNames.' neu angelegt';
            } else {
                $newUsersMessage = count($userData['newUsers']).' Benutzer werden mit '.(count($newGlobalTags) === 1 ? 'dem Tag ' : 'den TAGs ').$tagNames.' neu angelegt';
            }
        }
        $data['actions']['newUsers'] = [
            'message' => $newUsersMessage,
            'data' => collect($userData['newUsers'])->map($convertToUsername),
        ];
        $data['actions']['updateUsers'] = [
            'message' => count($userData['updateUsers']) === 1 ? '1 Benutzer wird aktualisiert' : count($userData['updateUsers']).' Benutzer werden aktualisiert',
            'data' => collect($userData['updateUsers'])->map($convertToUsername),
        ];
        $data['actions']['deleteUsers'] = [
            'message' => count($userData['deleteUsers']) === 1 ? '1 Benutzer wird gelöscht' : count($userData['deleteUsers']).' Benutzer werden gelöscht',
            'data' => collect($userData['deleteUsers'])->map(function ($user) {
                return $user['existingUser']->getDisplayNameBackend();
            }),
        ];
        $data['actions']['identicalUsers'] = [
            'message' => count($userData['identicalUsers']) === 1 ? '1 Benutzer bleibt unverändert' : count($userData['identicalUsers']).' Benutzer bleiben unverändert',
            'data' => collect($userData['identicalUsers'])->map($convertToUsername),
        ];

        $tagData = $this->collectTagChanges();
        $data['actions']['newTags'] = [
            'message' => count($tagData['newTags']) === 1 ? '1 neuer TAG wird angelegt' : count($tagData['newTags']).' neue TAGs werden angelegt',
            'data' => collect($tagData['newTags'])->map(function ($tag) {
                return $tag['label'];
            }),
        ];

        $invalidTagCount = count($tagData['invalidTags']);
        if ($invalidTagCount > 0) {
            $message = $invalidTagCount === 1 ? '1 TAG existiert bereits, ist aber nicht der korrekten TAG Gruppe zugeordnet' : $invalidTagCount.' TAGs existieren bereits, sind aber nicht der korrekten TAG Gruppe zugeordnet';
            $data['errors'][] = [
                'message' => $message,
                'data' => $tagData['invalidTags'],
            ];
        }

        return $data;
    }

    /**
     * Generates an array of user changes.
     *
     * @return array
     * @throws InvalidDataException
     */
    private function collectUserChanges()
    {
        $data = [
            'newUsers' => [],
            'updateUsers' => [],
            'deleteUsers' => [],
            'identicalUsers' => [],
        ];
        $compareHeader = $this->additionalData['compareHeader'] ?? 'mail';

        if ($compareHeader === 'mail') {
            $emails = collect($this->users)->map(function ($user) {
                return strtolower($this->getDataPoint($user, $this->headers, 'mail'));
            });
            $existingUsers = User::ofApp($this->app->id)
                ->where(function ($query) use ($emails) {
                    foreach ($emails as $email) {
                        $query->orWhereRaw('LOWER(email) = ?', [$email]);
                    }
                })
                ->with(['metafields', 'tags'])
                ->get()
                ->keyBy(function ($user) {
                    return strtolower($user->email);
                });
            $stillExistingUsers = [];
            foreach ($this->users as $user) {
                $usersEmail = strtolower($this->getDataPoint($user, $this->headers, 'mail'));
                $stillExistingUsers[$usersEmail] = true;
                if ($existingUsers->has($usersEmail)) {
                    // This is an existing user
                    if(!$this->creatorHasPermission($existingUsers->get($usersEmail))) {
                        continue;
                    }

                    if ($this->userIsUnchanged($user, $existingUsers->get($usersEmail))) {
                        $data['identicalUsers'][] = [
                            'userData' => $user,
                        ];
                    } else {
                        $data['updateUsers'][] = [
                            'userData' => $user,
                            'existingUser' => $existingUsers->get($usersEmail),
                        ];
                    }
                } else {
                    // This is a new user
                    $data['newUsers'][] = [
                        'userData' => $user,
                    ];
                }
            }
            if ($this->additionalData['deleteUsers']) {
                $allExistingUsers = User::ofApp($this->app->id)
                    ->with(['metafields', 'tags'])
                    ->get();
                foreach ($allExistingUsers as $existingUser) {
                    if (! isset($stillExistingUsers[strtolower($existingUser->email)]) && ! $existingUser->is_dummy && !$existingUser->is_api_user && !$existingUser->is_api_user) {
                        $data['deleteUsers'][] = [
                            'existingUser' => $existingUser,
                        ];
                    }
                }
            }
        } else {
            /** @var Collection|User[] $existingUsers */
            $existingUsers = User::ofApp($this->app->id)
                ->with(['metafields', 'tags'])
                ->get();
            $compareMetaKey = substr($compareHeader, strlen('meta_'));
            $stillExistingUsers = [];
            foreach ($this->users as $newUserData) {
                $usersMetaData = $this->getDataPoint($newUserData, $this->headers, $compareHeader);
                $stillExistingUsers[$usersMetaData] = true;
                $existingUser = $existingUsers->first(function ($user) use ($usersMetaData, $compareMetaKey) {
                    return $user->getMeta($compareMetaKey) == $usersMetaData;
                });

                if ($existingUser) {
                    // This is an existing user
                    if(!$this->creatorHasPermission($existingUser)) {
                        continue;
                    }

                    if ($this->userIsUnchanged($newUserData, $existingUser)) {
                        $data['identicalUsers'][] = [
                            'userData' => $newUserData,
                        ];
                    } else {
                        $data['updateUsers'][] = [
                            'userData' => $newUserData,
                            'existingUser' => $existingUser,
                        ];
                    }
                } else {
                    // This is a new user
                    $data['newUsers'][] = [
                        'userData' => $newUserData,
                    ];
                }
            }

            if ($this->additionalData['deleteUsers']) {
                foreach ($existingUsers as $existingUser) {
                    if (! isset($stillExistingUsers[$existingUser->getMeta($compareMetaKey)]) && ! $existingUser->is_dummy && !$existingUser->is_api_user) {
                        $data['deleteUsers'][] = [
                            'existingUser' => $existingUser,
                        ];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Check if the import ($newUserData) wants to change the $existingUser.
     *
     * @param $newUserData
     * @param User $existingUser
     * @return bool
     * @throws InvalidDataException
     */
    private function userIsUnchanged($newUserData, User $existingUser)
    {
        if ($this->additionalData['compareHeader'] !== 'mail' && $this->hasData($this->headers, 'mail')) {
            $emailDifferent = strtolower($existingUser->email) !== strtolower($this->getDataPoint($newUserData, $this->headers, 'mail'));
            if ($emailDifferent) {
                return false;
            }
        }

        if ($this->hasData($this->headers, 'language')) {
            $languageDifferent = $existingUser->language !== $this->getDataPoint($newUserData, $this->headers, 'language');
            if ($languageDifferent) {
                return false;
            }
        }

        $usernameDifferent = $existingUser->username !== $this->getUsername($newUserData, $this->headers);
        if ($usernameDifferent) {
            return false;
        }

        if ($this->hasData($this->headers, 'firstname')) {
            if ($existingUser->firstname !== $this->getDataPoint($newUserData, $this->headers, 'firstname')) {
                return false;
            }
        }
        if ($this->hasData($this->headers, 'lastname')) {
            if ($existingUser->lastname !== $this->getDataPoint($newUserData, $this->headers, 'lastname')) {
                return false;
            }
        }

        $metaDifferent = count($this->getUpdatedMetaKeys($existingUser, $newUserData)) > 0;

        if ($metaDifferent) {
            return false;
        }

        $quizTeam = $this->additionalData['quizTeam'];
        if ($quizTeam) {
            $hasNewGQuizTeam = ! $existingUser->quizTeams->contains($quizTeam);
            if ($hasNewGQuizTeam) {
                return false;
            }
        }

        $newTags = $this->getNewTagIds($existingUser, $newUserData);
        if ($newTags !== null) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of meta keys which values contain updated data.
     *
     * @param $existingUser
     * @param $newUserData
     * @return array
     * @throws InvalidDataException
     */
    private function getUpdatedMetaKeys(User $existingUser, $newUserData)
    {
        $updatedMetaKeys = [];
        $userMeta = $existingUser->getMeta();
        $metaFields = collect($this->app->getUserMetaDataFields(true))
            ->filter(function ($metaField) {
                return (bool) $metaField['import'];
            });
        foreach ($metaFields->keys() as $metaKey) {
            // If the user didn't select this meta field in the import we don't care about it
            if (!$this->hasData($this->headers, 'meta_' . $metaKey)) {
                continue;
            }
            $newMetaValue = $this->getDataPoint($newUserData, $this->headers, 'meta_' . $metaKey);

            if (!isset($userMeta[$metaKey])) {
                if ($newMetaValue) {
                    $this->checkUniqueMetaField($metaKey, $newMetaValue);
                    // The user doesn't have this meta key yet, but the import wants to set it
                    $updatedMetaKeys[] = 'meta_' . $metaKey;
                }
            } else {
                if ($userMeta[$metaKey] !== $newMetaValue) {
                    $this->checkUniqueMetaField($metaKey, $newMetaValue);
                    // The import wants to set a new meta value for this key
                    $updatedMetaKeys[] = 'meta_' . $metaKey;
                }
            }
        }

        return $updatedMetaKeys;
    }

    /**
     * Returns an array of tag group ids for which the tags have been updated.
     *
     * @param User $existingUser
     * @param $newUserData
     *
     * @return array
     * @throws InvalidDataException
     */
    private function getNewTagIds(User $existingUser, $newUserData)
    {
        $newGlobalTags = $this->additionalData['tags'];
        $newIndividualTags = [];
        $removeTags = [];

        $idx = 0;
        foreach ($this->app->tagGroups->pluck('id') as $tagGroupId) {
            // If the user didn't select this tag group in the import we don't care about it
            if (! $this->hasData($this->headers, 'tag_group_'.$tagGroupId)) {
                continue;
            }
            $newTagGroupValue = $this->getDataPoint($newUserData, $this->headers, 'tag_group_'.$tagGroupId);
            $existingTagGroupTag = $existingUser->tags->firstWhere('tag_group_id', $tagGroupId);

            if ($existingTagGroupTag) {
                if (strlen($newTagGroupValue) === 0) {
                    // The user already has a tag from this tag group, but the new value is empty, so we remove it.
                    $removeTags[] = $existingTagGroupTag->id;
                } else {
                    if ($existingTagGroupTag->label !== $newTagGroupValue) {
                        // The user already has a tag from this tag group, but we want to give them a different one.
                        $removeTags[] = $existingTagGroupTag->id;
                        $newTag = $this->app->tags->firstWhere('label', $newTagGroupValue);
                        if ($newTag) {
                            $newIndividualTags[] = $newTag->id;
                        } else {
                            // We do this "fake id" when we are only checking the import and not actually importing it,
                            // because when we execute the actual import the new tags are created beforehand and when we
                            // are only checking the import they aren't
                            $newIndividualTags[] = 'x'.$idx++;
                        }
                    }
                }
            } else {
                if (strlen($newTagGroupValue) > 0) {
                    // The user doesn't have a tag from this tag group but should now have one.
                    $newTag = $this->app->tags->firstWhere('label', $newTagGroupValue);
                    if ($newTag) {
                        $newIndividualTags[] = $newTag->id;
                    } else {
                        // We do this "fake id" when we are only checking the import and not actually importing it,
                        // because when we execute the actual import the new tags are created beforehand and when we
                        // are only checking the import they aren't
                        $newIndividualTags[] = 'x'.$idx++;
                    }
                }
            }
        }

        if ($newGlobalTags) {
            $newTags = array_unique(array_merge($newGlobalTags->pluck('id')->toArray(), $newIndividualTags));
        } else {
            $newTags = $newIndividualTags;
        }

        if(!$this->creator->isFullAdmin()) {
            $creatorTagRights = $this->creator->tagRightsRelation->pluck('id');
            $newTags = $creatorTagRights->intersect($newTags)->toArray();
            $removeTags = $creatorTagRights->intersect($removeTags)->toArray();
        }

        if ($newTags || $removeTags) {
            $currentTags = $existingUser->tags->pluck('id')->toArray();
            // Combine the current tags and the new tags
            $newTags = collect(array_unique(array_merge($currentTags, $newTags)));
            // Remove the tags which the user should no longer have
            $newTags = $newTags->filter(function ($tagId) use ($removeTags) {
                return ! in_array($tagId, $removeTags);
            });

            // Return all new tags
            $diffTags = $newTags->filter(function ($item) use ($currentTags) {
                return ! in_array($item, $currentTags);
            });
            if ($diffTags->count() == 0) {
                return null;
            }

            return $newTags->toArray();
        } else {
            // Null means nothing changed
            return null;
        }
    }

    /**
     * Collect new tags which need to be added and checks if there are any invalid tags.
     *
     * @return array
     * @throws InvalidDataException
     */
    private function collectTagChanges()
    {
        $data = [
            'newTags' => [],
            'invalidTags' => [],
        ];
        $tagGroups = $this->getTagGroups();
        $existingTags = Tag::where('app_id', $this->app->id)
            ->select('label', 'tag_group_id')
            ->get()
            ->keyBy('label');
        foreach ($tagGroups as $tagGroup) {
            $tags = [];
            $tagGroupId = intval(substr($tagGroup, strlen('tag_group_')));
            foreach ($this->users as $user) {
                $tag = $this->getDataPoint($user, $this->headers, $tagGroup);
                if ($tag) {
                    $tags[$tag] = true;
                }
            }
            foreach (array_keys($tags) as $tag) {
                if ($existingTags->has($tag)) {
                    if ($existingTags->get($tag)->tag_group_id != $tagGroupId) {
                        // A tag with this label exists already but is assigned a different (or no) tag group
                        $data['invalidTags'][] = $tag;
                    }
                } else {
                    $data['newTags'][] = [
                        'label' => $tag,
                        'tag_group_id' => $tagGroupId,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Returns tag group headers.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTagGroups()
    {
        return collect($this->headers)->filter(function ($header) {
            return Str::startsWith($header, 'tag_group_');
        });
    }

    /**
     * Returns the user data (combined first- and lastname).
     *
     * @param $newUserData
     *
     * @return string
     * @throws InvalidDataException
     */
    private function getUsername($newUserData)
    {
        $username = $this->getDataPoint($newUserData, $this->headers, 'firstname');
        if ($this->hasData($this->headers, 'lastname')) {
            $username .= ' '.$this->getDataPoint($newUserData, $this->headers, 'lastname');
        }

        return $username;
    }

    /**
     * Sends all emails which we saved earlier.
     * We need to do this outside of the main import transaction, because we only want to send the emails
     * when the import was successful. Also, as the import runs in a transaction, the user updates might not be available
     * when the email job runs before the main import job has finished.
     */
    private function sendEmails()
    {
        if (! $this->additionalData['dontInviteUsers']) {
            foreach ($this->invitationEmails as $invitationEmail) {
                $this->mailer->sendAppInvitation($invitationEmail['appId'], $invitationEmail['email'], $invitationEmail['userId'], $invitationEmail['password']);
            }
        }
        foreach ($this->newQuizTeamUserIds as $newQuizTeamUserId) {
            $this->mailer->sendNewQuizTeamNotification($newQuizTeamUserId, $this->additionalData['quizTeam']);
        }
    }

    private function creatorHasPermission(User $existingUser) {
        if($this->creator->isFullAdmin()) {
            return true;
        }
        $creatorTagRights = $this->creator->tagRightsRelation->pluck('id');
        $availableTags = $creatorTagRights->intersect($existingUser->tags->pluck('id'));
        return $availableTags->isNotEmpty();
    }

    private function checkUniqueMetaField(string $metaKey, string $metaValue) {
        if (in_array($metaKey, $this->uniqueMetaFields)) {
            $existingUsers = User::getByMetafield($this->app->id, $metaKey, $metaValue);
            if ($existingUsers->count()) {
                app()->abort(400, 'The meta field ' . $metaKey . ' must be unique. Another user has the value "'. $metaValue . '"');
            }
        }
    }
}
