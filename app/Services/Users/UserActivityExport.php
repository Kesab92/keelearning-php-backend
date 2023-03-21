<?php

namespace App\Services\Users;

use App\Models\AnalyticsEvent;
use App\Models\LearnBoxCardUserDailyCount;
use App\Models\Like;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class UserActivityExport
{
    const DECIMAL_POINTS = 5;

    private CarbonImmutable $from;
    private CarbonImmutable $to;
    /** @var CarbonImmutable[] $days */
    private array $days;

    public function __construct(CarbonInterface $from, CarbonInterface $to)
    {
        $this->from = $from->toImmutable()->startOfDay();
        $this->to = $to->toImmutable()->endOfDay();

        $this->days = [];
        $dayIterator = $this->from->toMutable();
        while ($dayIterator->lessThanOrEqualTo($this->to)) {
            $this->days[] = $dayIterator->toImmutable();
            $dayIterator->addDay();
        }
    }

    /**
     * Returns the data for each usergroup.
     * key: usergroup title
     * `queryCallback`: callback to manipulate the query,
     * returning it for easier chainability
     * `countUsers`: callback which gets handed a list of all users sorted (desc) by creation date and returns
     * an integer for how many of those match the usergroup
     *
     * @return array
     */
    private function usergroups(): array
    {
        return [
            'Alle User' => [
                'queryCallback' => function (Builder $query, string $dayField) {
                    return $query->whereRaw('users.created_at <= ' . $dayField);
                },
                'countUsers' => function (array $users, CarbonImmutable $date) {
                    // This code is a bit counterintuitive, but it works like this:
                    // Count all users which have been created after the given date
                    // Subtract those users from the total count of the users
                    $notInGroup = 0;
                    $cutOffDate = $date->toDateTimeString();
                    foreach($users as $created_at) {
                        if($created_at < $cutOffDate) {
                            break;
                        }
                        $notInGroup++;
                    }
                    return count($users) - $notInGroup;
                },
            ],
            '<= 1 Monat registriert' => [
                'queryCallback' => function (Builder $query, string $dayField) {
                    return $query->whereRaw('users.created_at BETWEEN DATE_SUB(' . $dayField . ', INTERVAL 30 DAY) AND ' . $dayField);
                },
                'countUsers' => function (array $users, CarbonImmutable $date) {
                    $count = 0;
                    $cutOffDate = $date->clone()->subDays(30)->toDateTimeString();
                    $startDate = $date->toDateTimeString();
                    foreach($users as $created_at) {
                        // If the user was created after the date that we are looking at, we ignore them
                        if($created_at > $startDate) {
                            continue;
                        }
                        // We also ignore all users that have been created before the earliest day we want to take into consideration
                        if($created_at < $cutOffDate) {
                            break;
                        }
                        $count++;
                    }
                    return $count;
                },
            ],
            '<= 3 Monate registriert' => [
                'queryCallback' => function (Builder $query, string $dayField) {
                    return $query->whereRaw('users.created_at BETWEEN DATE_SUB(' . $dayField . ', INTERVAL 90 DAY) AND ' . $dayField);
                },
                'countUsers' => function (array $users, CarbonImmutable $date) {
                    $count = 0;
                    $cutOffDate = $date->clone()->subDays(90)->toDateTimeString();
                    $startDate = $date->toDateTimeString();
                    foreach($users as $created_at) {
                        if($created_at > $startDate) {
                            continue;
                        }
                        if($created_at < $cutOffDate) {
                            break;
                        }
                        $count++;
                    }
                    return $count;
                },
            ],
            '<= 6 Monate registriert' => [
                'queryCallback' => function (Builder $query, string $dayField) {
                    return $query->whereRaw('users.created_at BETWEEN DATE_SUB(' . $dayField . ', INTERVAL 180 DAY) AND ' . $dayField);
                },
                'countUsers' => function (array $users, CarbonImmutable $date) {
                    $count = 0;
                    $cutOffDate = $date->clone()->subDays(180)->toDateTimeString();
                    $startDate = $date->toDateTimeString();
                    foreach($users as $created_at) {
                        if($created_at > $startDate) {
                            continue;
                        }
                        if($created_at < $cutOffDate) {
                            break;
                        }
                        $count++;
                    }
                    return $count;
                },
            ],
            '> 6 Monate registriert' => [
                'queryCallback' => function (Builder $query, string $dayField) {
                    return $query->whereRaw('users.created_at < DATE_SUB(' . $dayField . ', INTERVAL 180 DAY)');
                },
                'countUsers' => function (array $users, CarbonImmutable $date) {
                    $notInGroup = 0;
                    $cutOffDate = $date->clone()->subDays(180)->toDateTimeString();
                    foreach($users as $created_at) {
                        if($created_at < $cutOffDate) {
                            break;
                        }
                        $notInGroup++;
                    }
                    // We want the amount users that have been created before 180 days ago, notInGroup contains
                    // the amount of users that have been created in the last 180 days.
                    return count($users) - $notInGroup;
                },
            ],
        ];
    }

    /**
     * Returns the data needed on a per-row basis.
     * key: title of the row
     * `query`: callback that creates the base query
     * `datefield`: string giving the database field to
     * compare our desired date range against
     * `per_user`: will the result be a group of results per user,
     * otherwise a single number is expected
     *
     * @return array
     */
    private function statistics(): array
    {
        return [
            'App genutzt' => [
                'query' => function () {
                    return AnalyticsEvent
                        ::selectRaw('count(DISTINCT analytics_events.user_id) as data')
                        ->where('analytics_events.type', '!=', AnalyticsEvent::TYPE_USER_CREATED)
                        ->leftJoin('users', function ($joinQuery) {
                            $joinQuery->on('users.id', '=', 'analytics_events.user_id');
                        });
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => false,
            ],
            'Bearbeitete Powerlearning-Karten' => [
                'query' => function () {
                    return LearnBoxCardUserDailyCount
                        ::selectRaw('SUM(learn_box_card_user_daily_counts.count) AS count')
                        ->leftJoin('users', function ($joinQuery) {
                            $joinQuery->on('users.id', '=', 'learn_box_card_user_daily_counts.user_id');
                        });
                },
                'datefield' => 'learn_box_card_user_daily_counts.date',
                'per_user' => true,
            ],
            'Quiz-Spiele gegen Menschen gestartet' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_QUIZ_START_VS_HUMAN);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Quiz-Spiele gegen Bots gestartet' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_QUIZ_START_VS_BOT);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Mediathek-Aufrufe' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_VIEW_LEARNING_MATERIAL);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'News-Aufrufe' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_VIEW_NEWS);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Likes verteilt' => [
                'query' => function () {
                    return Like
                        ::selectRaw('count(*) as count')
                        ->leftJoin('users', function ($joinQuery) {
                            $joinQuery->on('users.id', '=', 'likes.user_id');
                        });
                },
                'datefield' => 'likes.created_at',
                'per_user' => true,
            ],
            'App-Feedback eingereicht' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_FEEDBACK_SENT);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Kommentare geschrieben' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_COMMENT_ADDED);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Kurse erfolgreich abgeschlossen' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_COURSE_SUCCESS);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
            'Tests erfolgreich abgeschlossen' => [
                'query' => function () {
                    return $this->countAnalyticsEventsQuery(AnalyticsEvent::TYPE_TEST_SUCCESS);
                },
                'datefield' => 'analytics_events.created_at',
                'per_user' => true,
            ],
        ];
    }

    /**
     * Returns an array containing:
     * `columns`, array of strings for table headers
     * `rows`, array of arbitrary values to put in each row's cells
     *
     * @return array
     */
    public function get(): array
    {
        $columns = [
            'Tag',
        ];
        $statistics = $this->statistics();
        $userGroups = $this->usergroups();

        // Fetch user group sizes
        $userGroupSizes = [];
        // Fetch a list of the created_at dates of all active
        /** @var string[] $activeUsers */
        $activeUsers = $this
            ->activeUsersQueryRestriction(User::select('created_at'))
            ->toBase() // We don't want Laravel to cast the dates to carbon objects
            ->get()
            ->sortByDesc('created_at')
            ->pluck('created_at')
            ->toArray();
        foreach ($userGroups as $usergroupTitle => $usergroupData) {
            $userGroupSizes[$usergroupTitle] = [];
            foreach ($this->days as $day) {
                $userGroupSizes[$usergroupTitle][$day->toDateString()] = $usergroupData['countUsers']($activeUsers, $day);
            }
            $columns[] = 'Gesamtanzahl :: ' . $usergroupTitle;
        }

        foreach ($statistics as $statsTitle => $statsInfo) {
            foreach ($userGroups as $usergroupTitle => $usergroupData) {
                $columnTitle = $statsTitle . ' :: ' . $usergroupTitle;
                $columns[] = $columnTitle;
                if ($statsInfo['per_user']) {
                    $columns[] = $columnTitle . ' :: Standardabweichung';
                }
            }

        }

        $rows = [];
        $dailyEntries = [];
        foreach ($userGroups as $usergroupTitle => $usergroupData) {
            foreach ($statistics as $statsTitle => $statsInfo) {
                $query = $this->makeCellQuery($usergroupData, $statsInfo)
                    ->getQuery();
                if ($statsInfo['per_user']) {
                    $dailyEntries[$usergroupTitle . ' :: ' . $statsTitle] = $query
                        ->get()
                        ->groupBy('day')
                        ->map->map(function ($day) {
                            return $day->count;
                        });
                } else {
                    $dailyEntries[$usergroupTitle . ' :: ' . $statsTitle] = $query
                        ->pluck('data', 'day');
                }
            }
        }

        foreach ($this->days as $day) {
            $row = [
                $day->toDateString(),
            ];
            foreach ($userGroups as $usergroupTitle => $usergroupData) {
                $row[] = $userGroupSizes[$usergroupTitle][$day->toDateString()];
            }
            foreach ($statistics as $statsTitle => $statsInfo) {
                foreach ($userGroups as $usergroupTitle => $usergroupData) {
                    $dataForThisDay = $dailyEntries[$usergroupTitle . ' :: ' . $statsTitle]->get($day->toDateString());
                    if ($dataForThisDay) {
                        if ($statsInfo['per_user']) {
                            $groupSize = $userGroupSizes[$usergroupTitle][$day->toDateString()];
                            $row[] = number_format($dataForThisDay->sum() / $groupSize, self::DECIMAL_POINTS, ',', '.');
                            $standardDeviation = $this->calculateStandardDeviation($dataForThisDay, $groupSize);
                            $row[] = number_format($standardDeviation, self::DECIMAL_POINTS, ',', '.');
                        } else {
                            $row[] = $dataForThisDay;
                        }
                    } else {
                        $row[] = 0;
                        if ($statsInfo['per_user']) {
                            // Add another cell for the std deviation
                            $row[] = 0;
                        }
                    }
                }
            }
            $rows[] = $row;
        }
        return [
            'columns' => $columns,
            'rows' => $rows,
        ];
    }

    /**
     * Calculates the standard deviation, given an collection of floats
     *
     * @param Collection $population
     * @return float
     */
    private function calculateStandardDeviation(Collection $population, ?int $total = null): float
    {
        $size = $total ?? $population->count();
        $average = $population->sum() / $size;
        $variance = $population->reduce(function ($carry, $entry) use ($average) {
            return $carry + pow(($entry - $average), 2);
        }, 0.0);

        return sqrt($variance / $size);
    }

    /**
     * Builds a standard query to count analytic events
     *
     * @param integer $analyticsEventType
     * @return Builder
     */
    private function countAnalyticsEventsQuery(int $analyticsEventType): Builder
    {
        return AnalyticsEvent
            ::selectRaw('count(*) as count')
            ->where('analytics_events.type', $analyticsEventType)
            ->leftJoin('users', function ($joinQuery) {
                $joinQuery->on('users.id', '=', 'analytics_events.user_id');
            })
            ->groupBy('analytics_events.user_id');
    }

    /**
     * Constructs the query for a given cell
     *
     * @param array $usergroupData
     * @param array $statsInfo
     * @return Builder
     */
    private function makeCellQuery(array $usergroupData, array $statsInfo): Builder
    {
        /** @var Builder $baseQuery */
        $baseQuery = $usergroupData['queryCallback']($statsInfo['query'](), $statsInfo['datefield']);
        return $baseQuery
            ->whereBetween($statsInfo['datefield'], [$this->from, $this->to])
            ->selectRaw('date_format(' . $statsInfo['datefield'] . ', "%Y-%m-%d") as day')
            ->groupByRaw('date_format(' . $statsInfo['datefield'] . ', "%Y-%m-%d")');
    }

    private function activeUsersQueryRestriction(Builder $query): Builder
    {
        return $query->where('users.is_bot', 0)
            ->where('users.is_dummy', 0)
            ->where('users.is_api_user', 0)
            ->whereNull('users.deleted_at')
            ->where('users.tos_accepted', 1)
            ->where('users.active', 1)
            ->where('users.is_keeunit', 0);
    }
}
