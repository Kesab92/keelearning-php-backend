<?php

namespace App\Models;

use App\Models\Comments\Comment;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Services\AppProfiles\AppProfileCache;
use App\Services\AppSettings;
use App\Traits\Saferemovable;
use App\Traits\TagRights;
use Auth;
use Cache;
use DateTimeInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User.
 *
 * @property-read \App\Models\App $app
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gameAsPlayer1
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Game[] $gameAsPlayer2
 * @property int $id
 * @property int $app_id
 * @property string $username
 * @property string $email
 * @property bool $active
 * @property string $password
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $tos_accepted
 * @property string $remember_token
 * @property bool $is_admin
 * @property bool $is_bot
 * @property string $language
 * @property string $fcm_id
 * @property string $apns_id
 * @property string $gcm_id_browser
 * @property string $gcm_browser_p256dh
 * @property string $gcm_browser_auth
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User butNotThisOne()
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User ofSameApp()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuizTeam[] $quizTeams
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereAppId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTosAccepted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereActive($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User active()
 * @property string $tag_ids
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereGcmId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereTagIds($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $createdTags
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tags
 * @property string|null $deleted_at
 * @property string|null $country
 * @property int $failed_login_attempts
 * @property int $is_dummy
 * @property string $firstname
 * @property string $lastname
 * @property string|null $avatar
 * @property string|null $avatar_url
 * @property \datetime|null $expires_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AccessLog[] $accessLogs
 * @property-read int|null $access_logs_count
 * @property-read int|null $created_tags_count
 * @property-read int|null $game_as_player1_count
 * @property-read int|null $game_as_player2_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GamePoint[] $gamePoints
 * @property-read int|null $game_points_count
 * @property-read mixed $permissions_list
 * @property-read int|null $quiz_team_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\LearnBoxCard[] $learnBoxCards
 * @property-read int|null $learn_box_cards_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserPermission[] $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QuestionDifficulty[] $questionDifficulties
 * @property-read int|null $question_difficulties_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Reminder[] $reminders
 * @property-read int|null $reminders_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SuggestedQuestion[] $suggestedQuestions
 * @property-read int|null $suggested_questions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Tag[] $tagRights
 * @property-read int|null $tag_rights_count
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TestSubmission[] $testSubmissions
 * @property-read int|null $test_submissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TrainingAnswer[] $trainingAnswers
 * @property-read int|null $training_answers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\VoucherCode[] $voucherCodes
 * @property-read int|null $voucher_codes_count
 * @method static Builder|User activeOfApp($appId)
 * @method static Builder|User admin()
 * @method static Builder|User bot()
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User ofApp($appId)
 * @method static Builder|User powerlessAdmin()
 * @method static Builder|User query()
 * @method static Builder|User tagRights($tagIds = null)
 * @method static Builder|User tagRightsJoin($tagIds = null)
 * @method static Builder|User whereApnsId($value)
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereAvatarUrl($value)
 * @method static Builder|User whereCountry($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereExpiresAt($value)
 * @method static Builder|User whereFailedLoginAttempts($value)
 * @method static Builder|User whereFcmId($value)
 * @method static Builder|User whereFirstname($value)
 * @method static Builder|User whereGcmBrowserAuth($value)
 * @method static Builder|User whereGcmBrowserP256dh($value)
 * @method static Builder|User whereGcmIdBrowser($value)
 * @method static Builder|User whereIsAdmin($value)
 * @method static Builder|User whereIsBot($value)
 * @method static Builder|User whereIsDummy($value)
 * @method static Builder|User whereLanguage($value)
 * @method static Builder|User whereLastname($value)
 * @mixin IdeHelperUser
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use Saferemovable;
    use TagRights;

    const AVATAR_SIZE = 250;

    private static $_cachedRandomAvatars;
    private static $_cachedAppSpecificAvatars;
    private static $_cachedAppSpecificBots;
    private static $_cachedApps;

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected $hidden = [
        'active',
        'apns_id',
        'created_at',
        'deleted_at',
        'fcm_id',
        'gcm_browser_auth',
        'gcm_browser_p256dh',
        'gcm_id_browser',
        'is_admin',
        'password',
        'remember_token',
        'tos_accepted',
        'updated_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_admin' => 'boolean',
        'expires_at' => 'datetime:Y-m-d',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('human', function (Builder $builder) {
            $builder->where('is_bot', 0);
        });

        static::created(function($user) {
            if (!$user->avatar_url) {
                $user->avatar_url = $user->getDefaultAvatar();
                $user->save();
            }
        });
    }

    public function app()
    {
        return $this->belongsTo(App::class);
    }

    public function quizTeams()
    {
        return $this->belongsToMany(QuizTeam::class, 'quiz_team_members')->withTimestamps();
    }
    public function role()
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    public function metafields()
    {
        return $this->hasMany(UserMetafield::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(UserPermission::class);
    }

    public function voucherCodes()
    {
        return $this->hasMany(VoucherCode::class);
    }

    public function individualCourses()
    {
        return $this->belongsToMany(Course::class, 'course_individual_attendees', 'user_id', 'course_id')->withTimestamps();
    }

    public function hasPermission($permission)
    {
        throw new Exception('Call to deprecated method hasPermission.');
    }

    public function hasRight($permission)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->role && $this->role->hasRight($permission)) {
            return true;
        }
        return false;
    }

    public function getAllRights(): array
    {
        $forceValue = null;
        if ($this->is_main_admin || $this->isSuperAdmin()) {
            $forceValue = true;
        } elseif (!$this->role) {
            $forceValue = false;
        }
        return collect(UserRoleRight::RIGHT_TYPES)->map(function ($right) use ($forceValue) {
            return [
                'key' => $right,
                'value' => $forceValue !== null ? $forceValue : $this->hasRight($right),
            ];
        })->pluck('value', 'key')->toArray();
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    public function gamePoints()
    {
        return $this->hasMany(GamePoint::class);
    }

    public function learnBoxCards()
    {
        return $this->hasMany(LearnBoxCard::class);
    }

    public function questionDifficulties()
    {
        return $this->hasMany(QuestionDifficulty::class);
    }

    public function suggestedQuestions()
    {
        return $this->hasMany(SuggestedQuestion::class);
    }

    public function testSubmissions()
    {
        return $this->hasMany(TestSubmission::class);
    }

    public function trainingAnswers()
    {
        return $this->hasMany(TrainingAnswer::class);
    }

    public function openIdTokens()
    {
        return $this->hasMany(OpenIdToken::class);
    }

    public function authTokens()
    {
        return $this->hasMany(AuthToken::class);
    }

    public function courseParticipations()
    {
        return $this->hasMany(CourseParticipation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tagRightsRelation()
    {
        return $this->belongsToMany(Tag::class, 'user_tag_rights')->withTimestamps();
    }

    public function privacyNoteConfirmations()
    {
        return $this->hasMany(PrivacyNoteConfirmation::class);
    }

    public function scopeShowInLists($query)
    {
        return $query->where('is_bot', 0)
            ->where('is_dummy', 0)
            ->where('is_api_user', 0);
    }

    public function getQuestionCategories($scope = CategoryHider::SCOPE_QUIZ, $withIndexCardType = false)
    {
        $categories = Category::ofApp($this->app_id)
            ->where('categories.active', 1)
            ->doesntHave('tags')
            ->when($scope !== null, function ($q) use ($scope) {
                $q->whereDoesntHave('hiders', function ($q) use ($scope) {
                    $q->where('scope', $scope);
                });
            })
            ->whereHas($withIndexCardType ? 'allQuestions' : 'questions', function ($query) {
                $query->where('visible', 1);
            })
            ->get();
        $usersTagIds = $this->tags->pluck('id');
        if ($usersTagIds->count() > 0) {
            $tagCategories = Category
                ::where('categories.app_id', $this->app_id)
                ->select('categories.*')
                ->where('categories.active', 1)
                ->join('category_tag', 'categories.id', '=', 'category_tag.category_id')
                ->whereIn('category_tag.tag_id', $usersTagIds)
                ->when($scope !== null, function ($q) use ($scope) {
                    $q->whereDoesntHave('hiders', function ($q) use ($scope) {
                        $q->where('scope', $scope);
                    });
                })
                ->whereHas($withIndexCardType ? 'allQuestions' : 'questions', function ($query) {
                    $query->where('visible', 1);
                })
                ->get();
            $categories = $categories->merge($tagCategories);
        }

        if ($categories->count()) {
            // Make sure to not display categories where the categorygroup isn't available for the user
            $categorygroups = $this->getQuestionCategorygroups();
            $categories = $categories->filter(function ($category) use ($categorygroups) {
                return ! $category->categorygroup_id || $categorygroups->contains('id', $category->categorygroup_id);
            });
        }

        return $categories;
    }

    public function getQuestionCategoriesGrouped($scope = CategoryHider::SCOPE_QUIZ, $withIndexCardType = false)
    {
        $gameCategorygroups = Categorygroup::ofApp($this->app_id)
            ->has('categories')
            ->with('translationRelation')
            ->with('categories.translationRelation')
            ->get();
        $gameCategorygroups = new Collection($gameCategorygroups->toArray());

        // Filter categories from groups which are not playable
        $playerCategories = $this->getQuestionCategories($scope, $withIndexCardType);

        $gameCategorygroups = $gameCategorygroups->map(function ($gameCategorygroup) use (&$playerCategories) {
            $gameCategorygroup['categories'] = array_values(array_filter($gameCategorygroup['categories'], function ($category) use ($playerCategories) {
                return $playerCategories->contains('id', $category['id']);
            }));

            return $gameCategorygroup;
        });

        // Remove category groups where no categories are playable
        $gameCategorygroups = $gameCategorygroups->filter(function ($gameCategorygroup) {
            return count($gameCategorygroup['categories']) > 0;
        });

        return $gameCategorygroups->toArray();
    }

    public function getQuestionCategorygroups()
    {
        $categorygroups = Categorygroup::ofApp($this->app_id)
            ->doesntHave('tags')
            ->has('categories')
            ->get();

        $usersTagIds = $this->tags->pluck('id');
        if ($usersTagIds->count() > 0) {
            $tagCategorygroups = Categorygroup
                ::ofApp($this->app_id)
                ->select('categorygroups.*')
                ->join('categorygroup_tag', 'categorygroups.id', '=', 'categorygroup_tag.categorygroup_id')
                ->whereIn('categorygroup_tag.tag_id', $usersTagIds)
                ->has('categories')
                ->get();
            $categorygroups = $categorygroups->merge($tagCategorygroups);
        }

        return $categorygroups;
    }

    public function getIndexCardCategories()
    {
        $categories = Category::ofApp($this->app_id)
            ->where('categories.active', 1)
            ->doesntHave('tags')
            ->has('indexCards')
            ->get();
        foreach ($this->tags as $tag) {
            $categories = $categories->merge($tag->categories()
                ->where('categories.active', 1)
                ->has('indexCards')
                ->get());
        }

        $appSettings = new AppSettings($this->app_id);
        if ($categories->count()) {
            if ($appSettings->getValue('use_subcategory_system')) {
                // Make sure to not display categories where the categorygroup isn't available for the user
                $categorygroups = $this->getQuestionCategorygroups();
                $categories = $categories->filter(function ($category) use ($categorygroups) {
                    return ! $category->categorygroup_id || $categorygroups->contains('id', $category->categorygroup_id);
                })->each(function ($item) use ($categorygroups) {
                    $item->categorygroup_name = $categorygroups
                        ->filter(function ($group) use ($item) {
                            return $group->id === $item->categorygroup_id;
                        })
                        ->pluck('name')
                        ->first();
                })->values();
            } else {
                $categories = $categories->map(function ($category) {
                    $category->categorygroup_id = null;
                    return $category;
                });
            }
        }

        return $categories;
    }

    /**
     * Returns a relation of tags, that were CREATED BY the current user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdTags()
    {
        return $this->hasMany(Tag::class, 'creator_id', 'id');
    }

    public function gameAsPlayer1()
    {
        return $this->hasMany(Game::class, 'player1_id');
    }

    public function gameAsPlayer2()
    {
        return $this->hasMany(Game::class, 'player2_id');
    }

    /**
     * The function returns true or false whether the user is allowed to play or not. This depends on the game state.
     *
     * @param Game $game
     * @param null $userId
     * @return bool
     */
    public static function isAllowedToPlay(Game $game, $userId = null)
    {
        $userIsPlayer1Or2 = self::isPlayer1Or2($game, $userId);

        // The current user has to be either player 1 or 2
        if ($userIsPlayer1Or2['userIsPlayer1']) {
            if ($game->status == Game::STATUS_TURN_OF_PLAYER_1) {
                return true;
            }
        } elseif ($userIsPlayer1Or2['userIsPlayer2']) {
            if ($game->status == Game::STATUS_TURN_OF_PLAYER_2) {
                return true;
            }
        }

        return false;
    }

    /**
     * The function returns an array of booleans for the user being player 1 or 2.
     *
     * @param Game $game
     * @param null $userId
     * @return array
     */
    public static function isPlayer1Or2(Game $game, $userId = null)
    {

        // If there was given no input parameter
        if ($userId == null) {
            $userId = user()->id;
        }

        // Set variables to determine if user is player 1 or 2 or nothing
        $userIsPlayer1 = $game->player1_id == $userId;
        $userIsPlayer2 = $game->player2_id == $userId;

        return [
            'userIsPlayer1' => $userIsPlayer1,
            'userIsPlayer2' => $userIsPlayer2,
        ];
    }

    /**
     * Returns an array of all avatars for a
     * given app – taking app-specific avatar settings into account.
     * @param App $app
     * @return array
     */
    public static function getDefaultAvatars(App $app): array
    {
        $avatars = null;
        $avatarPath = '/img/avatars/';

        if ($app->useSpecificAvatars()) {
            $avatarPath = '/img/avatars/'.App::SLUGS[$app->id].'/';
            if (file_exists(public_path().$avatarPath)) {
                if (! isset(self::$_cachedAppSpecificAvatars[$app->id])) {
                    self::$_cachedAppSpecificAvatars[$app->id] = glob(public_path().$avatarPath.'*.*');
                }
                $avatars = self::$_cachedAppSpecificAvatars[$app->id];
            }
        }
        if (! $avatars) {
            if (! self::$_cachedRandomAvatars) {
                self::$_cachedRandomAvatars = glob(public_path().$avatarPath.'*.*');
            }
            $avatars = self::$_cachedRandomAvatars;
        }

        return collect($avatars)
            ->map(function ($filename) {
                return collect(explode('/', $filename))->last();
            })
            ->filter()
            ->map(function ($filename) use ($avatarPath) {
                return backendPath().$avatarPath.$filename;
            })
            ->all();
    }

    /**
     * Gets a random avatar for the current app, based on a unique id.
     * If you like to use app specific avatars, place images in /public/img/avatars/{appId}
     * Otherwise this method will use default avatars from /public/img/avatars.
     *
     * @param App $app
     * @param $id
     * @return string
     */
    public static function getRandomAvatar(App $app, $id): string
    {
        $avatars = self::getDefaultAvatars($app);
        $index = $id % (count($avatars) - 1);

        return $avatars[$index];
    }

    /**
     * Scope the query to only return other users than the one with the given id. if none is given, take the currently
     * logged user.
     *
     * @param $query
     * @param null $userId
     * @return mixed
     */
    public function scopeButNotThisOne($query, $userId = null)
    {
        if (! $userId) {
            $userId = user()->id;
        }

        return $query->where('id', '!=', $userId);
    }

    /**
     * Scope queries users of this given app id.
     * @param $query
     * @param $appId
     * @return
     */
    public function scopeOfApp($query, $appId)
    {
        return $query->where('app_id', $appId);
    }

    /**
     * Scope queries active users of this given app id.
     * @param $query
     * @param $appId
     * @return
     */
    public function scopeActiveOfApp($query, $appId)
    {
        return $query->where('app_id', $appId)
            ->whereNull('deleted_at')
            ->where('is_dummy', 0)
            ->active();
    }

    /**
     * Scope queries users that are bots.
     * @param $query
     * @return
     */
    public function scopeBot($query)
    {
        return $query->withoutGlobalScope('human')->where('is_bot', '>', 0);
    }

    /**
     * Scope the query to only return a user that belongs to the app with the given id. take the current user's app_id
     * if this parameter is not given.
     *
     * @param $query
     * @param null $appId
     * @return mixed
     */
    public function scopeOfSameApp($query, $appId = null)
    {
        if (! $appId) {
            $appId = user()->app_id;
        }

        return $query->where('app_id', '=', $appId);
    }

    /**
     * Scope the results to a user that is active.
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    public static function getSuperAdminIds()
    {
        return explode(',', env('SUPERADMIN_USER_IDS'));
    }

    /**
     * Checks if the user is a superadmin
     * Superadmins can manage all apps.
     *
     * @return mixed
     */
    public function isSuperAdmin()
    {
        if (!in_array($this->id, self::getSuperAdminIds())) {
            return false;
        }
        if ($this->app_id !== App::ID_KEEUNIT_HR) {
            return false;
        }
        if (!$this->is_admin || $this->tagRightsRelation->count()) {
            return false;
        }
        return true;
    }

    /**
     * Checks if the user has full access, or is limited to specific TAGs.
     *
     * @return bool
     */
    public function isFullAdmin()
    {
        if ($this->isMainAdmin()) {
            return true;
        }

        return $this->tagRightsRelation->count() === 0;
    }

    public function isMainAdmin()
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        if ($this->role && $this->role->is_main_admin) {
            return true;
        }
        return false;
    }

    public function scopePowerlessAdmin($query)
    {
        return $query->where('is_admin', true)
            ->whereNotIn('id', self::getSuperAdminIds())
            ->whereHas('role', function ($query) {
                $query->where('is_main_admin', false);
                $query->whereDoesntHave('rights');
            });
    }

    public function scopeAdmin($query)
    {
        return $query->where('is_admin', true)
            ->orWhereIn('id', self::getSuperAdminIds());
    }

    public function scopeWithoutMainAdmins($query)
    {
        $mainAdminRolesQuery = UserRole::select('id')
            ->where('is_main_admin', true);

        return $query->whereNotIn('user_role_id', $mainAdminRolesQuery)
            ->orWhereNull('user_role_id');
    }

    /**
     * @return App
     */
    public function getApp()
    {
        if (! isset(self::$_cachedApps[$this->app_id])) {
            self::$_cachedApps[$this->app_id] = App::find($this->app_id);
        }

        return self::$_cachedApps[$this->app_id];
    }

    /**
     * Returns the URL to the bot picture (respects custom bots).
     */
    public static function getBotAvatar(App $app, int $botNumber)
    {
        $pathHead = '/img/bots';
        $pathTail = '/bot-'.$botNumber.'.jpg';
        $app_id = $app->id;

        if ($app->useSpecificBots()) {
            $cacheId = $app_id.'_'.$botNumber;

            // check if in cache
            $maybeCached = self::$_cachedAppSpecificBots[$cacheId] ?? null;
            if ($maybeCached) {
                return $maybeCached;
            }

            // not in cache
            $customBotPath = $pathHead.'/'.App::SLUGS[$app_id].$pathTail;
            if (file_exists(public_path().$customBotPath)) {
                return self::$_cachedAppSpecificBots[$cacheId] = backendPath().$customBotPath;
            }
        }

        return backendPath().$pathHead.$pathTail;
    }

    /**
     * Returns a path to the user's default avatar, to use if none is set.
     *
     * @return string
     */
    public function getDefaultAvatar(): string
    {
        if ($this->is_bot) {
            return self::getBotAvatar($this->app, (int) $this->is_bot);
        }

        if ($this->app_id == App::ID_BAYER && Str::endsWith($this->email, '@bayer.com')) {
            return backendPath().'/img/bayer-default-avatar.png';
        }
        if ($this->app_id == App::ID_SCHWAEBISCH_HALL) {
            return backendPath().'/img/fuchsquiz-default-avatar.jpg';
        }
        if ($this->app_id == App::ID_GENOAKADEMIE) {
            return backendPath().'/img/geno-default-avatar.png';
        }

        // select random avatar based on user ID
        return self::getRandomAvatar($this->app, $this->id);
    }

    /**
     * The function removes the available joker for the user.
     *
     *
     * @param Game $game
     * @param null $userId
     * @return bool
     */
    public function removeJoker(Game $game, $userId = null)
    {
        if ($userId == null) {
            $userId = user()->id;
        }

        $userIsPlayer1Or2 = $this->isPlayer1Or2($game, $userId);
        if ($userIsPlayer1Or2['userIsPlayer1']) {
            $game->player1_joker_available = 0;
            $game->save();

            return true;
        } elseif ($userIsPlayer1Or2['userIsPlayer2']) {
            $game->player2_joker_available = 0;
            $game->save();

            return true;
        }

        return false;
    }

    /**
     * Clears the cache for player stats.
     */
    public function clearStatsCache()
    {
        Cache::tags(['player-'.$this->id])->flush();
    }

    /**
     * Returns a string as a textual representation for the user, to be used in the api.
     *
     * @return string
     */
    public function getDisplayNameFrontend()
    {
        $appSettings = app(AppSettings::class, [
            'appId' => $this->app_id,
        ]);
        $appProfile = $this->getAppProfile();
        if ($appProfile->getValue('hide_emails_frontend')) {
            return $this->username;
        } else {
            return $this->username.' ('.$this->email.')';
        }
    }

    /**
     * Returns a string as a textual representation for the user, to be used in the backend.
     * CLONED TO STATS SERVER
     *
     * @return string
     */
    public function getDisplayNameBackend($showEmail = false)
    {
        $appSettings = app(AppSettings::class);
        $username = $this->username;
        if ($this->firstname && $this->lastname) {
            $username = $this->firstname . ' ' . $this->lastname;
        }
        if (!$appSettings->getValue('hide_emails_backend') && $showEmail) {
            if ($this->email) {
                $username .= ' ('.$this->email.')';
            }
        }
        return $username;
    }

    /**
     * Returns the name of the user (first + lastname); Fallback is User::getDisplayNameBackend()
     * includes mail address if not hidden.
     * @return string
     */
    public function getRealNameBackend($showEmail = false)
    {
        if (! $this->firstname || ! $this->lastname) {
            return $this->getDisplayNameBackend($showEmail);
        }

        $name = $this->firstname.' '.$this->lastname;
        if ($showEmail) {
            $name = $name.' ('.$this->email.')';
        }

        return $name;
    }

    /**
     * Returns the name of the user (first + lastname); Fallback is username.
     * @return string
     */
    public function getFullName()
    {
        if (! $this->firstname && ! $this->lastname) {
            return $this->username;
        }

        $name = $this->firstname.' '.$this->lastname;

        return $name;
    }

    public function hasPushNotificationId():bool
    {

        if(strlen($this->fcm_id) !== 0 || strlen($this->gcm_id_browser) !== 0 || strlen($this->apns_id) !== 0) {
            return true;
        }
        return FcmToken::where('user_id', $this->id)->count() > 0;
    }

    /**
     * Hide email addresses if temporary or dummy accounts
     * CLONED IN STATS SERVER
     *
     * @param string $value
     * @return string
     */
    public function getEmailAttribute(string $value): string
    {
        if (Str::startsWith($value, 'tmp') && Str::endsWith($value, '@sopamo.de')) {
            return 'Temporärer Account';
        } elseif (isDummyMail($value)) {
            return '';
        } else {
            return $value;
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function getMailFrontend()
    {
        $appProfile = $this->getAppProfile();
        if ($appProfile->getValue('hide_emails_frontend')) {
            return '';
        } else {
            return $this->email;
        }
    }

    public function getDisplaynameAttribute(): string
    {
        $appProfile = $this->getAppProfile();
        if (!$appProfile->getValue('use_real_name_as_displayname_frontend', false, true)) {
            return $this->username;
        }

        $displayname = $this->username;
        if ($this->firstname && $this->lastname) {
            $displayname = $this->firstname . ' ' . $this->lastname;
        }

        return $displayname;
    }

    public function getMailBackend()
    {
        $appSettings = new AppSettings($this->app_id);
        if ($appSettings->getValue('hide_emails_backend')) {
            return '';
        } else {
            return $this->email;
        }
    }

    /**
     * Checks if the user is a temporary one.
     *
     * @return bool
     */
    public function isTmpAccount()
    {
        if (Str::startsWith($this->email, 'tmp') && Str::endsWith($this->email, '@sopamo.de')) {
            return true;
        }
        if ($this->email == 'Temporärer Account') {
            return true;
        }

        return false;
    }

    /**
     * Checks if the user signed up without a mail.
     *
     * @return bool
     */
    public function isMaillessAccount()
    {
        if (isDummyMail($this->email)) {
            return true;
        }
        if ($this->email == '') {
            return true;
        }

        return false;
    }

    public function getLanguage()
    {
        return $this->language ?: defaultAppLanguage($this->app_id);
    }

    /**
     * The function logs a failed login attempt for a mail address.
     *
     * @param string $userMail
     */
    public static function failedLoginAttempt($userMail, $appId)
    {
        $user = self::where('app_id', $appId)->where('email', $userMail)->first();
        if ($user) {
            $user->failed_login_attempts += 1;
            $user->save();
        }
    }

    public function loginSuspended()
    {
        return $this->failed_login_attempts >= $this->app->getMaxFailedLoginAttempts();
    }

    /**
     * Return the app profile of this user.
     *
     * @return AppProfile
     */
    public function getAppProfile()
    {
        $appProfiles = AppProfileCache
            ::getAppProfiles($this->app_id)
            ->sortBy('is_default');

        return $appProfiles->filter(function (AppProfile $appProfile) {
            $appProfileTags = $appProfile->tags->pluck('id');

            if($appProfileTags->intersect($this->tags->pluck('id'))->isNotEmpty()) {
                return true;
            }

            return $appProfile->is_default;
        })->first();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function scopeTagRightsJoin($query, $tagIds = null)
    {
        if ($tagIds === null) {
            $tagIds = Auth::user()->tagRightsRelation->pluck('id');
        }

        if ($tagIds->count() == 0) {
            return $query->select('users.*');
        }

        return $query->select('users.*')
            ->leftJoin('tag_user', 'tag_user.user_id', '=', 'users.id')
            ->whereIn('tag_user.tag_id', $tagIds);
    }

    /**
     * Checks if the user is eligible for logging into the admin panel
     *
     * @return boolean
     */
    public function canAccessBackend(): bool
    {
        return (
            is_null($this->deleted_at)
            && !$this->is_bot
            && !$this->is_dummy
            && !$this->is_api_user
            && $this->active
            && $this->is_admin
        );
    }

    /**
     * Gets an user's given metafield,
     * or all as array if no key given
     *
     * @param string|null $key
     * @return array|string|null
     */
    public function getMeta(?string $key = null)
    {
        if (!$key) {
            return $this->metafields->pluck('value', 'key')->toArray();
        }
        $field = $this->metafields->where('key', $key)->first();
        return $field->value ?? null;
    }

    /**
     * Sets an user's metafield
     *
     * @param array|string $meta either key string, or associative array with key => value pairs
     * @param string|null $value new value for metafield, or empty if using array
     * @param bool $isRecursiveCall internally used
     */
    public function setMeta($meta, ?string $value = null, bool $isRecursiveCall = false): void
    {
        if (is_array($meta)) {
            foreach ($meta as $key => $value) {
                $this->setMeta($key, $value, true);
            }
            $this->unsetRelation('metafields');
            return;
        }
        $field = $this->metafields->where('key', $meta)->first();
        if (!$field) {
            $field = new UserMetafield;
            $field->user_id = $this->id;
            $field->key = $meta;
        }
        $field->value = $value;
        $field->save();
        if (!$isRecursiveCall) {
            $this->unsetRelation('metafields');
        }
    }

    /**
     * Deletes an user's metafield
     *
     * @param string $key
     * @return void
     */
    public function deleteMeta(string $key): void
    {
        $field = $this->metafields->where('key', $key)->first();
        if (!$field) {
            return;
        }
        $field->delete();
    }

    /**
     * Fetches users of a given app by a meta field value
     *
     * @param integer $appId
     * @param string $field
     * @param string $value
     * @return Collection
     */
    static public function getByMetafield(int $appId, string $field, string $value): Collection
    {
        return self::ofApp($appId)
            ->select('users.*')
            ->leftJoin('user_metafields', 'user_metafields.user_id', '=', 'users.id')
            ->where('user_metafields.key', $field)
            ->where('user_metafields.value', $value)
            ->get();
    }

    static public function clearStaticCache()
    {
        self::$_cachedRandomAvatars = null;
        self::$_cachedAppSpecificAvatars = null;
        self::$_cachedAppSpecificBots = null;
        self::$_cachedApps = null;
    }
}
