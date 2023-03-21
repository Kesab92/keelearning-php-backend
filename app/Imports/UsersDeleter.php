<?php

namespace App\Imports;

use App\Imports\Exceptions\InvalidDataException;
use App\Jobs\DeleteUsers;
use App\Models\AccessLog;
use App\Models\App;
use App\Models\Import;
use App\Models\User;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserDelete;
use App\Services\UserAnonymisation;
use DB;
use Illuminate\Support\Collection;

class UsersDeleter extends Importer
{
    protected $necessaryHeaders = [];

    /**
     * @var array An array with additional data we need for the import:
     *  [
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
    /** @var AccessLogEngine */
    private $accessLogEngine;
    private $creator;

    /**
     * @param $additionalData
     * @param $headers
     * @param $users
     * @throws \Exception
     * @throws \Throwable
     */
    protected function importData($additionalData, $headers, $users)
    {
        $this->additionalData = $additionalData;
        $this->headers = $headers;
        $this->users = $users;
        $this->app = App::find($this->additionalData['appId']);
        if ($this->additionalData['mode'] === DeleteUsers::MODE_NORMAL) {
            $this->import = Import::findOrFail($this->additionalData['importId']);
        }
        $this->accessLogEngine = app(AccessLogEngine::class);
        $this->creator = User::findOrFail($this->additionalData['creatorId']);

        DB::transaction(function () {
            $userChanges = [];
            if ($this->additionalData['mode'] === DeleteUsers::MODE_NORMAL) {
                $userChanges = $this->collectUserChanges();
                $this->stepDone();
            } elseif ($this->additionalData['mode'] === DeleteUsers::MODE_DELETE_ONLY) {
                $userChanges['deleteUsers'] = User::where('app_id', $this->app->id)
                    ->whereIn('id', $this->users)
                    ->with('tags')
                    ->get();
            }

            $this->deleteUsers($userChanges);
            $this->stepDone();
        });

        $this->importDone();
    }

    /**
     * Deletes users which don't exist in the uploaded csv.
     *
     * @param $userChanges
     * @throws \Exception
     */
    private function deleteUsers($userChanges)
    {
        /** @var UserAnonymisation $ua */
        $ua = app(UserAnonymisation::class);
        $idx = 0;
        $userDeletionCount = count($userChanges['deleteUsers']);
        foreach ($userChanges['deleteUsers'] as $deleteUser) {
            if($this->creatorHasPermission($deleteUser)) {
                if ($ua->anonymiseUser($deleteUser)) {
                    $this->accessLogEngine->log(AccessLog::ACTION_USER_DELETE, new AccessLogUserDelete($deleteUser), $this->additionalData['creatorId']);
                }
            }
            $this->setStepProgress($idx++ / $userDeletionCount);
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
        $this->creator = User::find($this->additionalData['creatorId']);
        $this->headers = $headers;
        $this->users = $users;
        $this->app = App::find($this->additionalData['appId']);
        $data = [
            'actions' => [],
            'errors' => [],
        ];
        $userData = $this->collectUserChanges();
        $data['actions']['deleteUsers'] = [
            'message' => count($userData['deleteUsers']) === 1 ? '1 Benutzer wird gelöscht' : count($userData['deleteUsers']).' Benutzer werden gelöscht',
            'data' => collect($userData['deleteUsers'])->map(function (User $user) {
                return $user->getDisplayNameBackend();
            }),
        ];

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
            'deleteUsers' => [],
        ];
        $compareHeader = $this->additionalData['compareHeader'] ?? 'mail';

        if ($compareHeader === 'mail') {
            $emails = collect($this->users)->map(function ($user) {
                return strtolower($this->getDataPoint($user, $this->headers, 'mail'));
            });
            $usersToDelete = User::ofApp($this->app->id)
                ->whereIn('email', $emails)
                ->with('tags')
                ->get();
            foreach ($usersToDelete as $user) {
                if ($this->creatorHasPermission($user)) {
                    $data['deleteUsers'][] = $user;
                }
            }
        } else {
            /** @var Collection|User[] $existingUsers */
            $existingUsers = User::ofApp($this->app->id)
                ->with(['metafields', 'tags'])
                ->get();
            $compareMetaKey = substr($compareHeader, strlen('meta_'));
            foreach ($this->users as $newUserData) {
                $usersMetaData = $this->getDataPoint($newUserData, $this->headers, $compareHeader);
                $existingUser = $existingUsers->first(function ($user) use ($usersMetaData, $compareMetaKey) {
                    return $user->getMeta($compareMetaKey) == $usersMetaData;
                });

                if ($existingUser && $this->creatorHasPermission($existingUser)) {
                    $data['deleteUsers'][] = $existingUser;
                }
            }
        }

        return $data;
    }

    private function creatorHasPermission(User $existingUser) {
        if($this->creator->isFullAdmin()) {
            return true;
        }
        $creatorTagRights = $this->creator->tagRightsRelation->pluck('id');
        $availableTags = $creatorTagRights->intersect($existingUser->tags->pluck('id'));
        return $availableTags->isNotEmpty();
    }
}
