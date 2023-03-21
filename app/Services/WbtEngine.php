<?php

namespace App\Services;

use App\Models\App;
use App\Models\Courses\Course;
use App\Models\Courses\CourseContent;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialTranslation;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use MongoCollection;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Cursor;

class WbtEngine
{
    const ROWS_MAX = 100;
    const CLOSING_EVENT_VERBS = [
        'http://adlnet.gov/expapi/verbs/completed',
        'http://adlnet.gov/expapi/verbs/failed',
        'http://adlnet.gov/expapi/verbs/passed',
    ];
    const STARTING_EVENT_VERBS = [
        'http://adlnet.gov/expapi/verbs/attempted',
        'http://adlnet.gov/expapi/verbs/experienced'
    ];
    const VERB_TO_STATUS = [
        'http://adlnet.gov/expapi/verbs/attempted' => 'attempted',
        'http://adlnet.gov/expapi/verbs/completed' => 'completed',
        'http://adlnet.gov/expapi/verbs/experienced' => 'experienced',
        'http://adlnet.gov/expapi/verbs/failed' => 'failed',
        'http://adlnet.gov/expapi/verbs/passed' => 'passed',
    ];

    const SORTABLES = [
        'date' => 'statement.timestamp',
        'score' => 'statement.result.score.scaled',
        'status' => 'statement.verb.id',
    ];

    private $appId;
    private $appSettings;
    private $learninglockerId;
    private $learningMaterialTranslations;
    /** @var \Illuminate\Database\Eloquent\Collection|User[] */
    private $cachedUsers;

    public function __construct($appId = null)
    {
        if (is_null($appId)) {
            $appId = appId();
        }
        $this->appId = $appId;
        $app = App::findOrFail($this->appId);
        $this->appSettings = new AppSettings($this->appId);
        $this->learninglockerId = $app->learninglocker_id;
        if (!$this->learninglockerId) {
            $errorMessage = 'No LearningLocker ID set for app #' . $this->appId;
            \Sentry::captureMessage($errorMessage);
            die($errorMessage);
        }
        $this->learningMaterialTranslations = LearningMaterialTranslation::
            whereHas('learningMaterial.learningMaterialFolder', function ($query) {
                $query->where('app_id', $this->appId);
            })
            ->get();
    }

    /**
     * @return \Jenssegers\Mongodb\Collection|MongoCollection
     */
    private function eventsQuery(): \Jenssegers\Mongodb\Collection
    {
        return DB::connection('learninglocker')
            ->getCollection('statements');
    }

    private function eventsQueryBuilder()
    {
        return DB::connection('learninglocker')
            ->collection('statements')
            ->select(['statement', 'relatedActivities'])
            ->where('organisation', new ObjectID($this->learninglockerId))
            ->whereIn('statement.verb.id', array_merge(self::CLOSING_EVENT_VERBS, self::STARTING_EVENT_VERBS));
    }

    public function formatStatement($statement)
    {
        $title = null;
        $courseContentId = null;
        $learningMaterialIds = null;
        $id = $statement['contextId'];
        if(is_array($id)) {
            // Raw data from mongodb returns the context id as an array
            $id = $id[0];
        }

        if ($id) {
            // WBTs in courses have `/course-content/${COURSE-CONTENT-ID}` appended
            // since we're taking the ID from the WBT where available,
            // multiple learning materials can share one wbt_id
            $idWithoutCourseContent = explode('/course-content/', $id)[0];
            $learningMaterialTranslations = $this->learningMaterialTranslations->where('wbt_id', $idWithoutCourseContent);
            if ($learningMaterialTranslations->count()) {
                $title = $learningMaterialTranslations->first()->title;
                $learningMaterialIds = $learningMaterialTranslations->pluck('learning_material_id')->toArray();
            }
            if (strpos($id, '/course-content/') !== false) {
                $idParts = explode('/', $id);
                $courseContentIdPosition = array_search('course-content', $idParts) + 1;
                $courseContentId = $idParts[$courseContentIdPosition];
            }
        }

        if (!$title) {
            // Fallback
            $title = $statement['fallbackTitle'];
        }
        $userId = $statement['userId'] ?? null; // See XapiController@buildActorObject

        $userName = $statement['userName'];
        if($userId && $this->cachedUsers) {
            /** @var User $user */
            $user = $this->cachedUsers->find($userId);
            if($user) {
                $userName = $user->getDisplayNameBackend();
            } else {
                $userName = 'Gelöschter Benutzer';
            }
        }

        return [
            'date' => $statement['timestamp'],
            'duration' => $statement['duration'] ?? null,
            'learning_material_ids' => $learningMaterialIds,
            'course_content_id' => $courseContentId,
            'score' => $statement['score'] ?? null,
            'status' => $statement['verb'] ?? 'started',
            'title' => $title,
            'user' => $userName,
            'user_id' => $userId,
        ];
    }

    /**
     * Undocumented function
     *
     * @param string|null $search string to search for in usernames and learning material titles
     * @param array|null $learningMaterialIds learningmaterials for which to fetch events
     * @param integer|null $courseId course for which to fetch events
     * @param User|null $adminUser requesting user to scope results to
     * @param string|null $orderBy field to order by
     * @param boolean $orderDesc order descendingly
     * @param int|null $page
     * @param int|null $rows
     * @return Collection
     */
    public function getEvents(
        ?string $search = null,
        ?array  $learningMaterialIds = null,
        ?int    $courseId = null,
        ?User   $adminUser = null,
        ?string $orderBy = null,
        bool    $orderDesc = false,
        ?int    $page = null,
        ?int    $rows = null,
        bool    $showPersonalData = false
    ): Collection
    {
        $searchWbtIds = $this->getSearchWbtIds($search);
        $permissionWbtIds = $this->getPermissionWbtIds($courseId, $learningMaterialIds);
        $searchUserIds = $this->getSearchUserIds($search, $showPersonalData);
        $permissionUserIds = $this->getPermissionUserIds($adminUser);

        $startDate = null;
        if($learningMaterialIds) {
            $startDate = $this->learningMaterialTranslations
                ->whereIn('learning_material_id', $learningMaterialIds)
                ->min('created_at');
        }

        $pipeline = [
            ...$this->selectAndGroup($searchWbtIds, $permissionWbtIds, $searchUserIds, $permissionUserIds, $startDate),
            ...$this->sort($orderBy, $orderDesc),
            ...$this->paginate($page, $rows),
        ];
        ['events' => $events, 'pagination' => $pagination] = $this->runPipeline($pipeline);

        $events = collect($events);

        $this->cachedUsers = User::activeOfApp($this->appId)
            ->whereIn('id', $events->pluck('userId'))
            ->get();

        $events = $events
            ->map(function ($event) {
                return $this->formatStatement($event);
            });

        $this->cachedUsers = null;

        if (!$showPersonalData) {
            $events->transform(function ($event) {
                unset($event['user']);
                unset($event['user_id']);
                return $event;
            });
        }

        return collect([
            'events' => $events,
            'eventcount' => $pagination['total'],
        ]);
    }

    /**
     * Executes the aggregation pipeline and returns a nice & simple array of events and pagination information
     *
     * @param array $pipeline
     * @return array
     */
    private function runPipeline(array $pipeline): array {
        $eventsQuery = $this->eventsQuery();
        /** @var Cursor $aggregation */
        $aggregation = $eventsQuery->aggregate($pipeline);

        // Yes this is an ugly solution, but it's very low code and all other solutions are really complicated.
        // Somehow people agree, that this is the best way to do it ¯\_(ツ)_/¯
        $aggregation = json_decode(json_encode($aggregation->toArray()), true)[0];

        $events = $aggregation['data'];
        $pagination = $aggregation['pagination'];
        $pagination = count($pagination) ? $pagination[0] : ['total' => 0];

        return [
            'events' => $events,
            'pagination' => $pagination,
        ];
    }

    /**
     * Returns an array of user ids for which to fetch events.
     * Returns null if we don't have to search for any users.
     *
     * @param null $search
     * @return array|null
     */
    private function getSearchUserIds($search = null, $showPersonalData): ?array {
        $filterBySearch = $showPersonalData && $search;

        if(!$filterBySearch) {
            return null;
        }
        return User::activeOfApp($this->appId)
            ->whereRaw('username LIKE ?', '%'.escapeLikeInput($search).'%')
            ->orWhereRaw('CONCAT_WS(" ", firstname, lastname) LIKE ?', '%'.escapeLikeInput($search).'%')
            ->orWhereRaw('email LIKE ?', '%'.escapeLikeInput($search).'%')
            ->pluck('id')
            ->map(function ($id) {
                // MongoDB Stores the user ids as strings
                return strval($id);
            })
            ->toArray();
    }

    /**
     * Returns an array of user ids for which to fetch events
     *
     * @param null $adminUser
     * @return array|null
     */
    private function getPermissionUserIds($adminUser = null): ?array {
        $isRestrictedAdmin = $adminUser && !$adminUser->isFullAdmin();

        if(!$isRestrictedAdmin) {
            return null;
        }
        return User::ofApp($this->appId)
                    ->tagRights($adminUser)
                    ->pluck('id')
                    ->map(function ($id) {
                        // MongoDB Stores the user ids as strings
                        return strval($id);
                    })
                    ->toArray();
    }

    /**
     * Returns an array of wbt ids for which to fetch events.
     * Returns null if we don't have to filter by wbt id.
     *
     * @param $courseId
     * @param $learningMaterialIds
     * @return array|null
     */
    private function getPermissionWbtIds($courseId, $learningMaterialIds): ?array
    {
        if(!$courseId && !$learningMaterialIds) {
            return null;
        }

        $wbtIds = collect([]);

        if ($learningMaterialIds && !$courseId) {
            $wbtIds = $this->learningMaterialTranslations
                ->whereIn('learning_material_id', $learningMaterialIds)
                ->pluck('wbt_id');
        }

        if ($courseId) {
            $course = Course::where('app_id', $this->appId)
                ->findOrFail($courseId);

            $courseContentsQuery = $course
                ->contents()
                ->where('type', CourseContent::TYPE_LEARNINGMATERIAL);

            if ($learningMaterialIds) {
                $courseContentsQuery->whereIn('foreign_id', $learningMaterialIds);
            } else {
                $courseContentsQuery->whereNotNull('foreign_id');
            }

            $courseContents = $courseContentsQuery->get();
            if (!$courseContents->count()) {
                return [];
            }

            if ($course->track_wbts) {
                // generate all possible WBT IDs for each course content
                $wbtIds = collect([]);
                foreach ($courseContents as $courseContent) {
                    $contentWbts = $this->learningMaterialTranslations
                        ->where('learning_material_id', $courseContent->foreign_id);
                    foreach ($contentWbts as $contentWbt) {
                        $wbtIds->push($contentWbt->wbt_id . '/course-content/' . $courseContent->id);
                    }
                }
            } else {
                // older courses don't track the wbt events per course content,
                // so we need to make sure to only get those belonging to visible course-users
                $wbtIds = $this->learningMaterialTranslations
                    ->whereIn('learning_material_id', $courseContents->pluck('foreign_id'))
                    ->pluck('wbt_id');
            }
        }

        return $wbtIds->toArray();
    }

    /**
     * Returns an array of wbt ids for which to fetch events.
     * Returns null if we don't have to filter by wbt id.
     *
     * @param null $search
     * @return array|null
     */
    private function getSearchWbtIds($search = null): ?array
    {
        if(!$search) {
            return null;
        }

        $wbtIds = $this->learningMaterialTranslations
            ->filter(function ($learningMaterialTranslation) use ($search) {
                return stripos($learningMaterialTranslation->title, $search) !== false;
            })
            ->pluck('wbt_id');

        return $wbtIds->toArray();
    }

    /**
     * Does the bulk of the work and returns the mongodb aggregation pipeline parts which are necessary to fetch the
     * event data
     *
     * @param array|null $wbtIds
     * @param array|null $searchUserIds
     * @param array|null $permissionUserIds
     * @param Carbon|null $startDate
     * @return array
     */
    private function selectAndGroup(?array $searchWbtIds, ?array $permissionWbtIds, ?array $searchUserIds, ?array $permissionUserIds, ?Carbon $startDate = null): array
    {
        $requirements = [
            [
                'organisation' => new ObjectID($this->learninglockerId)
            ],
        ];

        if($startDate) {
            $requirements[] =
                [
                    'statement.timestamp' => [
                        '$gt' => $startDate->format('Y-m-d H:i:s')
                    ]
                ];
        }

        if($permissionUserIds !== null) {
            $requirements[] = [
                'statement.actor.account.name' => [
                    '$in' => $permissionUserIds
                ]
            ];
        }

        if($permissionWbtIds !== null) {
            $requirements[] = [
                '$or' => [
                    [
                        'relatedActivities.0' => [
                            '$in' => $permissionWbtIds,
                        ],
                    ],
                ]
            ];
        }

        // If the search is active, we only want events which match at least a user or a wbt id
        if($searchUserIds !== null || $searchWbtIds) {
            $searchRequirements = [];
            if($searchUserIds !== null) {
                $searchRequirements[] = [
                    'statement.actor.account.name' => [
                        '$in' => $searchUserIds
                    ]
                ];
            }
            if($searchWbtIds !== null) {
                $searchRequirements[] = [
                    '$or' => [
                        [
                            'relatedActivities.0' => [
                                '$in' => $searchWbtIds,
                            ],
                        ],
                    ]
                ];
            }
            $requirements[] = [
                '$or' => $searchRequirements,
            ];
        }

        $select = [
            'timestamp' => [
                '$first' => '$statement.timestamp'
            ],
            'duration' => [
                '$first' => '$statement.result.duration'
            ],
            'userId' => [
                '$first' => '$statement.actor.account.name'
            ],
            'userName' => [
                '$first' => '$statement.actor.name'
            ],
            'contextId' => [
                '$first' => '$lastRelatedElement'
            ],
            'fallbackTitle' => [
                '$first' => '$statement.object.definition.name.und'
            ],
            'score' => [
                '$first' => '$statement.result.score.scaled'
            ]
        ];
        // These are the pipelines which are necessary to merge the two result groups ($part1 and $part2) together
        $merge = [
            [
                '$project' => [
                    'activity' => [
                        '$setUnion' => [
                            '$part1',
                            '$part2'
                        ]
                    ]
                ]
            ],
            [
                '$unwind' => '$activity'
            ],
            [
                '$replaceRoot' => [
                    'newRoot' => '$activity'
                ]
            ],
        ];
        return [
            [
                '$match' => [
                    '$and' => [
                        ...$requirements,
                    ]
                ]
            ],
            // Add the last element that's in the array of related activities
            // as this is the id of the actual WBT
            [
                '$addFields' => [
                    'lastRelatedElement' => [
                        '$slice' => [
                            '$relatedActivities',
                            -1
                        ]
                    ]
                ]
            ],
            [
                '$facet' => [
                    'part1' => [
                        [
                            '$match' => [
                                'statement.verb.id' => [
                                    '$in' => [
                                        'http://adlnet.gov/expapi/verbs/experienced',
                                        'http://adlnet.gov/expapi/verbs/attempted'
                                    ]
                                ]
                            ]
                        ],
                        [
                            '$group' => array_merge([
                                '_id' => [
                                    'actor' => '$statement.actor.account.name',
                                    'contextId' => '$lastRelatedElement'
                                ],
                            ], $select)
                        ]
                    ],
                    'part2' => [
                        [
                            '$match' => [
                                'statement.verb.id' => [
                                    '$in' => self::CLOSING_EVENT_VERBS,
                                ]
                            ]
                        ],
                        [
                            '$group' => array_merge([
                                '_id' => [
                                    'actor' => '$statement.actor.account.name',
                                    'verb' => '$statement.verb.id',
                                    'contextId' => '$lastRelatedElement'
                                ],
                                'verb' => [
                                    '$first' => '$statement.verb.id'
                                ],
                            ], $select)
                        ]
                    ]
                ]
            ],
            ...$merge,
        ];
    }

    /**
     * Adds aggregation pipeline parts which take care of sorting the events
     *
     * @param ?string $by
     * @param bool $desc
     * @return array
     */
    private function sort(?string $by, bool $desc = false): array
    {
        if(!$by) {
            return [];
        }
        $sortMap = [
            'date' => 'timestamp',
            'status' => 'verb',
            'score' => 'score',
        ];
        if(!isset($sortMap[$by])) {
            return [];
        }
        $sortKey = $sortMap[$by];
        return [
            [
                '$sort' => [
                    $sortKey => $desc ? -1 : 1,
                ]
            ]
        ];
    }

    private function paginate(?int $page = 0, ?int $rows = 0): array
    {
        $pagination = [
            [
                '$limit' => self::ROWS_MAX,
            ]
        ];
        if ($page) {
            if (!$rows || $rows > self::ROWS_MAX) {
                $rows = self::ROWS_MAX;
            }
            $pagination = [
                [
                    '$skip' => $rows * ($page - 1)
                ],
                [
                    '$limit' => $rows
                ]
            ];
        }

        return [
            [
                '$facet' => [
                    'data' => $pagination,
                    'pagination' => [
                        [
                            '$count' => 'total',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function prepareStatementForFormatting($event)
    {
        $relatedActivities = Arr::get($event, 'relatedActivities');
        return [
            'contextId' => $relatedActivities ? array_pop($relatedActivities) : null,
            'timestamp' => Arr::get($event, 'statement.timestamp'),
            'duration' => Arr::get($event, 'statement.duration'),
            'userId' => Arr::get($event, 'statement.actor.account.name'),
            'userName' => Arr::get($event, 'statement.actor.name'),
            'fallbackTitle' => Arr::get($event, 'statement.object.definition.name.und'),
            'score' => Arr::get($event, 'statement.result.score.scaled'),
            'verb' => Arr::get($event, 'statement.verb.id'),
        ];
    }

    public function formatStatementForFrontend($event)
    {
        $preparedStatement = $this->prepareStatementForFormatting($event);
        $formattedStatement = $this->formatStatement($preparedStatement);
        $status = $formattedStatement['status'];
        if (array_key_exists($status, self::VERB_TO_STATUS)) {
            $status = self::VERB_TO_STATUS[$status];
        }

        return [
            'date' => $formattedStatement['date'],
            'course_content_id' => $formattedStatement['course_content_id'] ?: null,
            'learning_material_id' => isset($formattedStatement['learning_material_ids']) ? $formattedStatement['learning_material_ids'][0] : null,
            'score' => is_numeric($formattedStatement['score']) ? round($formattedStatement['score'] * 100) : $formattedStatement['score'],
            'status' => $status,
        ];
    }

    /**
     * Returns all result wbt events for the given user and learning material.
     *
     * @param User $user
     * @param LearningMaterial $learningMaterial
     *
     * @return Collection
     */
    public function getUserLearningMaterialEvents(User $user, LearningMaterial $learningMaterial)
    {
        return $this->eventsQueryBuilder()
            ->where('statement.actor.account.name', (string)$user->id)
            ->where('relatedActivities.0', $learningMaterial->wbt_id)
            ->get()
            ->map(function ($event) {
                return $this->formatStatementForFrontend($event);
            });
    }

    /**
     * Returns all result wbt events for the given user and course content.
     *
     * @param User $user
     * @param CourseContent $courseContent
     *
     * @return Collection
     */
    public function getUserCourseContentEvents(User $user, CourseContent $courseContent)
    {
        $wbtIds = $this->learningMaterialTranslations
            ->where('learning_material_id', $courseContent->foreign_id)
            ->pluck('wbt_id');
        if (!$wbtIds->count()) {
            return collect();
        }
        if ($courseContent->course->track_wbts) {
            $wbtIds->transform(function ($wbtId) use ($courseContent) {
                return $wbtId . '/course-content/' . $courseContent->id;
            });
        }

        return $this->eventsQueryBuilder()
            ->where('statement.actor.account.name', (string)$user->id)
            ->where(function ($query) use ($wbtIds) {
                // Currently all WBTs have a maximum of 3 related activities.
                // Of course this might change, so this is a hack.
                // The problem is, that the only way to match to arrays (e.g. do an intersect) is using $expr
                // which can't use indizes.
                $query->whereIn('relatedActivities.0', $wbtIds);
            })
            ->get()
            ->map(function ($event) {
                return $this->formatStatementForFrontend($event);
            });
    }

    /**
     * Returns all result wbt events for the given user.
     *
     * @param User $user
     *
     * @return Collection
     */
    public function getUserEvents(User $user)
    {
        // We cache these events, because this data is requested on the dashboard and other "general" pages
        // in the frontend, so we want to reduce the db load a bit by potentially loading stale records.
        // The details pages in the frontend use the other methods to get their events, so there the
        // data will always be fresh.
        return \Cache::remember('all-wbt-user-events-' . $user->id, Carbon::now()->addMinutes(30), function() use ($user) {
            return $this->eventsQueryBuilder()
                ->where('statement.actor.account.name', (string)$user->id)
                ->get()
                ->map(function ($event) {
                    return $this->formatStatementForFrontend($event);
                });
        });

    }
}
