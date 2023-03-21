<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Comments\Comment;
use App\Models\Courses\Course;
use App\Models\Courses\CourseParticipation;
use App\Models\Game;
use App\Models\KeelearningModel;
use App\Models\SuggestedQuestion;
use App\Models\Test;
use App\Models\TestSubmission;
use App\Models\User;
use App\Models\Viewcount;
use App\Services\MorphTypes;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateEventData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyticsevents:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates analytics events, where possible, from existing data.';

    private $dummyUsers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Generating VIEWCOUNT events…');
        $viewcountTypes = [
            MorphTypes::TYPE_LEARNINGMATERIAL => AnalyticsEvent::TYPE_VIEW_LEARNING_MATERIAL,
            MorphTypes::TYPE_NEWS => AnalyticsEvent::TYPE_VIEW_NEWS,
            'App\Models\App' => AnalyticsEvent::TYPE_VIEW_HOME,
        ];
        foreach($viewcountTypes as $viewcountType => $analyticsEventType) {
            $morphType = $viewcountType;
            if($morphType === 'App\Models\App') {
                $morphType = MorphTypes::TYPE_APP;
            }
            $this->info('Starting type ' . $viewcountType);
            $viewcounts = Viewcount::select(DB::raw('viewcounts.*'))
                ->leftJoin('analytics_events', function ($join) use ($morphType, $analyticsEventType) {
                    $join->where('analytics_events.type', '=', $analyticsEventType)
                        ->on('analytics_events.foreign_id', '=', 'viewcounts.foreign_id')
                        ->on('analytics_events.user_id', '=', 'viewcounts.user_id')
                        ->where('analytics_events.foreign_type', '=', $morphType);
                })
                ->where('viewcounts.id', '>', 1887909)
                ->where('viewcounts.foreign_type', $viewcountType)
                ->whereNull('analytics_events.id');
            $bar = $this->output->createProgressBar($viewcounts->count());
            $bar->start();
            $viewcounts->with(['foreign', 'user.tags'])
                ->chunkById(100, function ($viewcounts) use ($analyticsEventType, $bar) {
                    $tags = collect();
                    foreach ($viewcounts as $viewcount) {
                        if($viewcount->foreign) {
                            $tags = $tags->mergeRecursive($this->createEvent(
                                $viewcount->user,
                                $analyticsEventType,
                                $viewcount->created_at,
                                $viewcount->foreign
                            ));
                        }
                        $bar->advance();
                    }
                    $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
                }, 'viewcounts.id', 'id');
            $bar->finish();
            $this->line('');
        }

        $this->line('Generating TYPE_COURSE_START events…');
        $courseParticipations = CourseParticipation::select(DB::raw('course_participations.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_COURSE_START)
                    ->on('analytics_events.foreign_id', '=', 'course_participations.course_id')
                    ->on('analytics_events.user_id', '=', 'course_participations.user_id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Course::class]);
            })
            ->join('users', 'users.id', '=', 'course_participations.user_id')
            ->whereNull('analytics_events.id');
        $bar = $this->output->createProgressBar($courseParticipations->count());
        $bar->start();
        $courseParticipations->with(['course.tags', 'user.tags'])
            ->chunkById(300, function ($courseParticipations) use ($bar) {
                $tags = collect();
                foreach ($courseParticipations as $courseParticipation) {
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $courseParticipation->user,
                        AnalyticsEvent::TYPE_COURSE_START,
                        $courseParticipation->created_at,
                        $courseParticipation->course
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'course_participations.id', 'id');
        $bar->finish();
        $this->line('');

        $this->line('Generating TYPE_COURSE_SUCCESS events…');
        $courseParticipations = CourseParticipation::select(DB::raw('course_participations.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_COURSE_SUCCESS)
                    ->on('analytics_events.foreign_id', '=', 'course_participations.course_id')
                    ->on('analytics_events.user_id', '=', 'course_participations.user_id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Course::class]);
            })
            ->whereNull('analytics_events.id')
            ->join('users', 'users.id', '=', 'course_participations.user_id')
            ->where('course_participations.passed', 1);
        $bar = $this->output->createProgressBar($courseParticipations->count());
        $bar->start();
        $courseParticipations
            ->with(['course.tags', 'user.tags'])
            ->chunkById(300, function ($courseParticipations) use ($bar) {
                $tags = collect();
                foreach ($courseParticipations as $courseParticipation) {
                    $createdAt = $courseParticipation->finished_at;
                    if(!$createdAt) {
                        // Probably due to a bug (that seems to be fixed already), there are some passed participations without finished_at date
                        // In that case, the updated at date is the best guess
                        $createdAt = $courseParticipation->updated_at;
                    }
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $courseParticipation->user,
                        AnalyticsEvent::TYPE_COURSE_SUCCESS,
                        $createdAt,
                        $courseParticipation->course
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'course_participations.id', 'id');
        $bar->finish();
        $this->line('');

        $botUserIds = User::bot()->pluck('id');

        $this->line('Generating TYPE_QUIZ_START_VS_HUMAN events…');
        $games = Game::select(DB::raw('games.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_QUIZ_START_VS_HUMAN)
                    ->on('analytics_events.foreign_id', '=', 'games.id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Game::class]);
            })
            ->whereNull('analytics_events.id')
            ->whereNotIn('player2_id', $botUserIds);
        $bar = $this->output->createProgressBar($games->count());
        $bar->start();
        $games
            ->with(['player1'])
            ->chunkById(300, function ($games) use ($bar) {
                $tags = collect();
                foreach ($games as $game) {
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $game->player1,
                        AnalyticsEvent::TYPE_QUIZ_START_VS_HUMAN,
                        $game->created_at,
                        $game
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'games.id', 'id');
        $bar->finish();
        $this->line('');

        $this->line('Generating TYPE_QUIZ_START_VS_BOT events…');
        $games = Game::select(DB::raw('games.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_QUIZ_START_VS_BOT)
                    ->on('analytics_events.foreign_id', '=', 'games.id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Game::class]);
            })
            ->whereNull('analytics_events.id')
            ->whereIn('player2_id', $botUserIds);
        $bar = $this->output->createProgressBar($games->count());
        $bar->start();
        $games
            ->with(['player1'])
            ->chunkById(300, function ($games) use ($bar) {
                $tags = collect();
                foreach ($games as $game) {
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $game->player1,
                        AnalyticsEvent::TYPE_QUIZ_START_VS_BOT,
                        $game->created_at,
                        $game
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'games.id', 'id');
        $bar->finish();
        $this->line('');

        $this->line('Generating TYPE_USER_CREATED events…');
        $usersQuery = User::select(DB::raw('users.*'))
            ->showInLists()
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_USER_CREATED)
                    ->on('analytics_events.user_id', '=', 'users.id');
            })
            ->whereNull('analytics_events.id');
        $bar = $this->output->createProgressBar($usersQuery->count());
        $bar->start();
        $usersQuery->chunkById(300, function ($users) use ($bar) {
            $tags = collect();
            foreach ($users as $user) {
                $tags = $tags->mergeRecursive($this->createEvent(
                    $user,
                    AnalyticsEvent::TYPE_USER_CREATED,
                    $user->created_at
                ));
                $bar->advance();
            }
            $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
        }, 'users.id', 'id');
        $bar->finish();
        $this->line('');

        $this->line('Generating TYPE_COMMENT_ADDED events…');
        $commentsQuery = Comment::select(DB::raw('comments.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_COMMENT_ADDED)
                    ->on('analytics_events.foreign_id', '=', 'comments.id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Comment::class]);
            })
            ->whereNull('analytics_events.id');
        $bar = $this->output->createProgressBar($commentsQuery->count());
        $bar->start();
        $commentsQuery->with('author')
            ->chunkById(300, function ($comments) use ($bar) {
                $tags = collect();
                foreach ($comments as $comment) {
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $comment->author,
                        AnalyticsEvent::TYPE_COMMENT_ADDED,
                        $comment->created_at,
                        $comment
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'comments.id', 'id');
        $bar->finish();
        $this->line('');

//        $this->line('Generating TYPE_QUESTION_SUGGESTED events…');
//        $suggestedQuestionsQuery = SuggestedQuestion::select(DB::raw('suggested_questions.*'))
//            ->leftJoin('analytics_events', function ($join) {
//                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_QUESTION_SUGGESTED)
//                    ->on('analytics_events.foreign_id', '=', 'suggested_questions.id')
//                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[SuggestedQuestion::class]);
//            })
//            ->whereNull('analytics_events.id');
//        $bar = $this->output->createProgressBar($suggestedQuestionsQuery->count());
//        $bar->start();
//        $suggestedQuestionsQuery
//            ->with('user')
//            ->chunkById(300, function ($suggestedQuestions) use ($bar) {
//                $tags = collect();
//                foreach ($suggestedQuestions as $suggestedQuestion) {
//                    if ($suggestedQuestion->user) {
//                        $tags = $tags->mergeRecursive($this->createEvent(
//                            $suggestedQuestion->user,
//                            AnalyticsEvent::TYPE_QUESTION_SUGGESTED,
//                            $suggestedQuestion->created_at,
//                            $suggestedQuestion
//                        ));
//                    }
//                    $bar->advance();
//                }
//                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
//            }, 'suggested_questions.id', 'id');
//        $bar->finish();
//        $this->line('');

        $this->line('Generating TYPE_TEST_SUCCESS events…');
        $testSubmissionsQuery = TestSubmission::select(DB::raw('test_submissions.*'))
            ->leftJoin('analytics_events', function ($join) {
                $join->where('analytics_events.type', '=', AnalyticsEvent::TYPE_TEST_SUCCESS)
                    ->on('analytics_events.foreign_id', '=', 'test_submissions.test_id')
                    ->where('analytics_events.foreign_type', '=', MorphTypes::MAPPING[Test::class]);
            })
            ->where('test_submissions.result', 1)
            ->whereNull('analytics_events.id');
        $bar = $this->output->createProgressBar($testSubmissionsQuery->count());
        $bar->start();
        $testSubmissionsQuery
            ->with('test')
            ->with('user')
            ->chunkById(300, function ($testSubmissions) use ($bar) {
                $tags = collect();
                foreach ($testSubmissions as $testSubmission) {
                    $tags = $tags->mergeRecursive($this->createEvent(
                        $testSubmission->user,
                        AnalyticsEvent::TYPE_TEST_SUCCESS,
                        $testSubmission->created_at,
                        $testSubmission->test
                    ));
                    $bar->advance();
                }
                $tags->each(function($entries, $table) {DB::table($table)->insert($entries);});
            }, 'test_submissions.id', 'id');
        $bar->finish();
        $this->line('');

    }

    /**
     * Creates a new analytics event
     *
     * @param User|null $user
     * @param integer $type
     * @param Carbon $createdAt
     * @param KeelearningModel|null $foreign
     * @return Collection
     */
    private function createEvent(?User $user, int $type, Carbon $createdAt, ?KeelearningModel $foreign = null): Collection
    {
        $analyticsEvent = new AnalyticsEvent;
        if($user) {
            $analyticsEvent->app_id = $user->app_id;
            $analyticsEvent->user_id = $user->id;
        } else {
            if(!$foreign) {
                throw new \Exception('Either user or foreign model must be provided');
            }
            if($foreign instanceof App) {
                $analyticsEvent->app_id = $foreign->id;
            } else {
                if(!$foreign->app_id) {
                    throw new \Exception('Foreign model has no app_id attribute');
                }
                $analyticsEvent->app_id = $foreign->app_id;
            }
            $analyticsEvent->user_id = $this->getDummyUser($analyticsEvent->app_id)->id;
        }
        if ($foreign) {
            $analyticsEvent->foreign()->associate($foreign);
        }
        $analyticsEvent->type = $type;
        $analyticsEvent->created_at = $createdAt;
        $analyticsEvent->save();

        $tagInserts = [
            'analytics_event_foreign_tag' => collect(), // The list of TAGs that the foreign model has
            'analytics_event_tag' => collect(), // The list of TAGs that both the foreign model and the user have
            'analytics_event_user_tag' => collect(), // The list of TAGs that the user has
        ];

        if ($foreign && $foreign->tags) {
            $tagInserts['analytics_event_foreign_tag'] = $foreign
                ->tags
                ->pluck('id');
            if ($user && $user->tags) {
                $tagInserts['analytics_event_tag'] = $user
                    ->tags
                    ->pluck('id')
                    ->intersect($foreign->tags->pluck('id'));
            } else {
                $tagInserts['analytics_event_tag'] = $foreign
                    ->tags
                    ->pluck('id');
            }
        } else {
            if($user) {
                $tagInserts['analytics_event_tag'] = $user
                    ->tags
                    ->pluck('id');
            }
        }
        if($user) {
            $tagInserts['analytics_event_user_tag'] = $user
                ->tags
                ->pluck('id');
        }
        return collect($tagInserts)
            ->map(function ($tagIds) use ($analyticsEvent) {
                return $tagIds->map(function ($tagId) use ($analyticsEvent) {
                    return [
                        'analytics_event_id' => $analyticsEvent->id,
                        'tag_id' => $tagId,
                    ];
                })->toArray();
            });
    }

    private function getDummyUser($appId) {
        if(!isset($this->dummyUsers[$appId])) {
            $this->dummyUsers[$appId] = User::where('app_id', $appId)->where('is_dummy', true)->first();
        }
        return $this->dummyUsers[$appId];
    }
}
