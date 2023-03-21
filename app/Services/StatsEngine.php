<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\App;
use App\Models\Category;
use App\Models\Game;
use App\Models\GameQuestionAnswer;
use App\Models\Question;
use App\Models\QuizTeam;
use App\Models\User;
use App\Stats\CategoryCorrect;
use App\Stats\CategoryWrong;
use App\Stats\ChallengingQuestions;
use App\Stats\NemesisPlayers;
use App\Stats\PlayerAnswerHistory;
use App\Stats\PlayerCorrectAnswers;
use App\Stats\PlayerCorrectAnswersByCategory;
use App\Stats\PlayerGameAborts;
use App\Stats\PlayerGameCount;
use App\Stats\PlayerGameDraws;
use App\Stats\PlayerGameLosses;
use App\Stats\PlayerGamesFinished;
use App\Stats\PlayerGameWins;
use App\Stats\PlayerPoints;
use App\Stats\PlayerQuestionBoxes;
use App\Stats\PlayerQuestionBoxesByCategory;
use App\Stats\PlayerTrainingAnswerHistory;
use App\Stats\PlayerTrainingCorrectAnswers;
use App\Stats\PlayerTrainingCorrectAnswersByCategory;
use App\Stats\PlayerTrainingWrongAnswers;
use App\Stats\PlayerTrainingWrongAnswersByCategory;
use App\Stats\PlayerWrongAnswers;
use App\Stats\PlayerWrongAnswersByCategory;
use App\Stats\QuestionCorrect;
use App\Stats\QuestionWrong;
use App\Stats\QuizProgress;
use App\Stats\QuizTeamCorrectAnswers;
use App\Stats\QuizTeamCorrectAnswersByCategory;
use App\Stats\QuizTeamGames;
use App\Stats\QuizTeamGameWins;
use App\Stats\QuizTeamWrongAnswers;
use App\Stats\StrongPlayers;
use Auth;
use Cache;
use Carbon\Carbon;
use Config;
use DB;
use Illuminate\Support\Collection;
use Log;

/**
 * Provides easy access to database statistics.
 */
class StatsEngine
{
    /**
     * @var null
     */
    private $appId;
    private $_questionPercentages;
    private $appSettings;
    private $tagRightIds;

    public function __construct($appId = null)
    {
        if (is_null($appId)) {
            $appId = appId();
        }
        $this->appId = $appId;
        $this->appSettings = new AppSettings($appId);
        if (app()->runningInConsole()) {
            $this->tagRightIds = [];
        } else {
            if (Auth::check()) {
                $this->tagRightIds = Auth::user()->tagRightsRelation()->pluck('user_tag_rights.tag_id');
            } else {
                $this->tagRightIds = null;
            }
        }
    }

    /**
     * Returns the count of users from an app which have an account.
     *
     * @return int
     */
    public function eligibleUsers()
    {
        $query = User::where('app_id', $this->appId)
            ->where('is_dummy', 0)
            ->where('is_api_user', 0)
            ->whereNull('deleted_at');

        if ($this->hasLimitedTagAccess()) {
            $query->whereHas('tags', function ($tagQuery) {
                return $tagQuery->whereIn('tags.id', $this->tagRightIds);
            });
        }

        return $query->count();
    }

    /**
     * Returns the count of users which accepted the terms of service.
     *
     * @return int
     */
    public function registeredUsers()
    {
        $query = User::where('app_id', $this->appId)
                   ->where('tos_accepted', 1)
                   ->whereNull('deleted_at');

        if ($this->hasLimitedTagAccess()) {
            $query->whereHas('tags', function ($tagQuery) {
                return $tagQuery->whereIn('tags.id', $this->tagRightIds);
            });
        }

        return $query->count();
    }

    /**
     * Returns the count of users which played at least one game.
     *
     * @return int
     */
    public function activeUsers()
    {
        $query = User::where('users.app_id', $this->appId)
                   ->where('is_dummy', 0)
                   ->where('is_api_user', 0)
                   ->where('tos_accepted', 1)
                   ->whereNull('deleted_at')
                   ->whereRaw('id IN (SELECT player1_id as player FROM games WHERE app_id = '.$this->appId.' UNION DISTINCT SELECT player2_id as player FROM games WHERE app_id = '.$this->appId.')');

        if ($this->hasLimitedTagAccess()) {
            $query->whereHas('tags', function ($tagQuery) {
                return $tagQuery->whereIn('tags.id', $this->tagRightIds);
            });
        }

        return $query->count();
    }

    /**
     * Returns the count of games played.
     *
     * @return mixed
     */
    public function games()
    {
        $query = Game::where('app_id', $this->appId)
                   ->whereIn('status', [Game::STATUS_FINISHED, Game::STATUS_CANCELED]);

        if ($this->hasLimitedTagAccess()) {
            $query->where(function ($innerQuery) {
                $innerQuery->whereHas('player1.tags', function ($tagQuery) {
                    return $tagQuery->whereIn('tags.id', $this->tagRightIds);
                })->orWhereHas('player2.tags', function ($tagQuery) {
                    return $tagQuery->whereIn('tags.id', $this->tagRightIds);
                });
            });
        }

        return $query->count();
    }

    /**
     * Returns all categories with the respective win counts.
     *
     * @return array ["Cat 1" => 0.5734, "Cat 2" => 0.7566]
     */
    public function categories()
    {
        return Cache::remember('stats-categories-'.$this->appId, 60 * 60 * 48, function () {
            $data = [];
            $correct = GameQuestionAnswer::join('question_answers', 'game_question_answers.question_answer_id', '=', 'question_answers.id')
                ->join('questions', 'questions.id', '=', 'question_answers.question_id')
                ->join('categories', 'categories.id', '=', 'questions.category_id')
                ->where('result', 1)
                ->where('categories.app_id', $this->appId)
                ->where('categories.active', true)
                ->where('questions.visible', 1)
                ->select('categories.id as id', DB::raw('COUNT(categories.id) as c'))
                ->groupBy('categories.id')
                ->pluck('c', 'id');
            $wrong = GameQuestionAnswer::join('question_answers', 'game_question_answers.question_answer_id', '=', 'question_answers.id')
                ->join('questions', 'questions.id', '=', 'question_answers.question_id')
                ->join('categories', 'categories.id', '=', 'questions.category_id')
                ->where('result', 0)
                ->where('categories.app_id', $this->appId)
                ->where('categories.active', true)
                ->where('questions.visible', 1)
                ->select('categories.id as id', DB::raw('COUNT(categories.id) as c'))
                ->groupBy('categories.id')
                ->pluck('c', 'id');
            foreach ($correct as $id => $correctCount) {
                $name = Category::find($id)->name;
                $wrongCount = isset($wrong[$id]) ? $wrong[$id] : 0;
                $data[$name] = ($correctCount / ($correctCount + $wrongCount)) * 100;
            }

            if (! $data) {
                return [];
            }

            arsort($data);
            // Show a max of 8 categories, otherwise show the top & flop 2
            if (count($data) <= 8) {
                return $data;
            }

            $i = -1;

            return array_filter($data, function () use (&$i, $data) {
                $i++;

                return $i < 2 || $i > count($data) - 3;
            });
        });
    }

    /**
     * Returns true if the user has tagids or no tag id restriction or he is superadmin.
     */
    public function hasLimitedTagAccess()
    {
        return ! isSuperAdmin() && count($this->tagRightIds);
    }

    /**
     * Amount of answered questions per calendar week.
     *
     * @param $userId
     *
     * @return array
     */
    public function getQuizProgress($user)
    {
        return (new QuizProgress($user))->fetch();
    }

    /**
     * Our 2 strongest enemies of the last 7 days.
     *
     * @param $user   User
     *
     * @return array
     */
    public function getNemesisPlayers(User $user)
    {
        return Cache::remember('nemesis-players-of-'.$user->id, 60 * 60 * 24, function () use ($user) {
            $userEngine = new UserEngine();
            $eligiblePlayers = $userEngine->getPossibleOpponents($user, false, false);
            $nemesisPlayers = (new NemesisPlayers($user->id))->fetch();
            // we're trying to get the first two strong opponents which are also in the eligible array
            $opponents = [];
            foreach ($nemesisPlayers as $nemesisPlayer) {
                if ($opponent = $eligiblePlayers->firstWhere('id', $nemesisPlayer['id'])) {
                    $opponents[] = [
                        'id'     => $opponent['id'],
                        'avatar' => $opponent['avatar'],
                        'name'   => $opponent['username'],
                        'wins'   => $nemesisPlayer['wins'],
                        'losses' => $nemesisPlayer['losses'],
                    ];
                    if (count($opponents) >= 2) {
                        return $opponents;
                    }
                }
            }

            return $opponents;
        });
    }

    /**
     * Strong players, measured by the last week.
     *
     * @param User $user
     *
     * @return array
     */
    public function getStrongPlayers(User $user)
    {
        return Cache::remember('strong-players-of-'.$user->app_id.'-against-'.implode(':', $user->tags->pluck('id')->toArray()), 60 * 60 * 24, function () use ($user) {
            $userEngine = new UserEngine();
            $eligiblePlayers = $userEngine->getPossibleOpponents($user, false, false);
            $strongPlayers = (new StrongPlayers($this->appId))->fetch();
            // we're trying to get the first two strong opponents which are also in the eligible array
            $opponents = [];
            foreach ($strongPlayers as $strongPlayer) {
                if ($opponent = $eligiblePlayers->firstWhere('id', $strongPlayer['id'])) {
                    $opponents[] = [
                        'id'     => $opponent['id'],
                        'avatar' => $opponent['avatar'],
                        'name'   => $opponent['username'],
                        'wins'   => $strongPlayer['wins'],
                        'losses' => $strongPlayer['losses'],
                    ];
                    if (count($opponents) >= 2) {
                        return $opponents;
                    }
                }
            }

            return $opponents;
        });
    }

    /**
     * Returns an array of challenging questions, in frontend-ready format.
     *
     * @return Collection
     */
    public function getChallengingQuestions(User $user)
    {
        if(!$user) {
            return collect([]);
        }
        $data = Cache::remember('challenging-questions-user-'.$user->id, 60 * 10, function () use ($user) {
            $rawQuestionData = (new ChallengingQuestions($this->appId))->fetch();

            // now only get the questions for the categories we have access to
            $relevantQuestionData = [];
            $availableCategories = $user->getQuestionCategories()->pluck('id');
            foreach ($availableCategories as $categoryId) {
                if (isset($rawQuestionData[$categoryId])) {
                    $relevantQuestionData = array_merge($relevantQuestionData, $rawQuestionData[$categoryId]);
                }
            }
            usort($relevantQuestionData, function ($a, $b) {
                if ($a['score'] == $b['score']) {
                    return 0;
                }

                return ($a['score'] > $b['score']) ? -1 : 1;
            });
            $questionData = collect(array_slice($relevantQuestionData, 0, 10));

            $questions = Question::whereIn('id', $questionData->pluck('id'))->get();
            $correctAnswers = GameQuestionAnswer::select('game_questions.question_id as id')
                                                ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
                                                ->where('user_id', $user->id)
                                                ->where('result', 1)
                                                ->where('game_question_answers.created_at', '>=', date('Y-m-d', strtotime('-1 week')))
                                                ->whereHas('gameQuestion', function ($q) use ($questionData) {
                                                    $q->whereIn('game_questions.question_id', $questionData->pluck('id'));
                                                })
                                                ->groupBy('id')
                                                ->pluck('id');

            return $questionData->map(function ($entry) use ($questions, $user, $correctAnswers) {
                $question = $questions->firstWhere('id', $entry['id']);
                if (! $question) {
                    return null;
                }
                $entry['failrate'] = round($entry['failrate'] * 100);
                if ($correctAnswers->contains($entry['id'])) {
                    $entry['hascorrect'] = true;
                }

                return $entry;
            })
                ->filter()
                ->take(3);
        });

        $questions = Question
            ::where('app_id', $user->app_id)
            ->whereIn('id', $data->pluck('id'))
            ->with(['category.translationRelation', 'translationRelation'])
            ->get();

        return $data->map(function ($entry) use ($questions) {
            $question = $questions->firstWhere('id', $entry['id']);
            if (! $question) {
                return null;
            }
            $entry['category'] = $question->category->name;
            $entry['title'] = $question->title;
            return $entry;
        })
            ->filter()
            ->take(3);
    }

    /**
     * Returns an array of challenging questions, circumventing cache.
     *
     * @return array
     */
    public function getChallengingQuestionsRaw()
    {
        return (new ChallengingQuestions($this->appId))->noCache()->fetch();
    }

    /**
     * Returns an array of strong players, circumventing cache.
     *
     * @return array
     */
    public function getStrongPlayersRaw()
    {
        return (new StrongPlayers($this->appId))->noCache()->fetch();
    }

    /**
     * Returns an array of questions with the respective percentages of correct answers
     * [question_id => ['title' => 'How much is the fish?", 'percentage' => 48], ... ].
     *
     * @return array
     */
    public function getQuestionPercentages()
    {
        return Cache::remember('stats-question-percentages-'.$this->appId, 60 * 60 * 48, function () {
            if ($this->_questionPercentages) {
                return $this->_questionPercentages;
            }
            // Prepare the results array
            // We save the correct answer count for each category in the field 1 and the wrong count in the field 0
            $results = [
                    0 => [],
                    1 => [],
            ];
            // Fetch the right/wrong answers for each question
            $questions = GameQuestionAnswer::select(DB::raw('count(*) as c'), 'questions.id', 'result')
                                           ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
                                           ->join('questions', 'questions.id', '=', 'game_questions.question_id')
                                           ->where('questions.app_id', $this->appId)
                                           ->where('questions.visible', 1)
                                           ->groupBy('result', 'questions.id')
                                           ->get();
            // Generate the results array
            foreach ($questions as $question) {
                if (! isset($results[$question->result][$question->id])) {
                    $results[$question->result][$question->id] = 0;
                }
                $results[$question->result][$question->id] = $question->c;
            }

            // Generate the percentages
            $percentages = [];
            foreach ($questions as $question) {
                if (! isset($percentages[$question->id])) {
                    // Fetch the counts for wrong and right answers
                    $wrong = 0;
                    if (isset($results[0][$question->id])) {
                        $wrong = $results[0][$question->id];
                    }
                    $right = 0;
                    if (isset($results[1][$question->id])) {
                        $right = $results[1][$question->id];
                    }
                    try {
                        $title = Question::find($question->id)->title;
                    } catch (\Exception $e) {
                        \Sentry::captureMessage('Translation for '.$question->id.' not found');
                        \Sentry::captureException($e);
                        $title = '';
                    }
                    if (! $right) {
                        // There are no right answers, 0 percent are correct
                        $percentages[$question->id] = [
                                'title'      => $title,
                                'percentage' => 0,
                        ];
                    } elseif (! $wrong) {
                        // There are no wrong answers, 100 percent are correct
                        $percentages[$question->id] = [
                                'title'      => $title,
                                'percentage' => 100,
                        ];
                    } else {
                        // There are right and wrong answers, calculate the percentage
                        $percentages[$question->id] = [
                                'title'      => $title,
                                'percentage' => ($right / ($wrong + $right)) * 100,
                        ];
                    }
                }
            }

            // Cache the result
            $this->_questionPercentages = $percentages;

            return $percentages;
        });
    }

    /**
     * Returns the top x questions
     * [question_id => ['title' => 'How much is the fish?", 'percentage' => 48], ... ].
     *
     * @param int $amount
     *
     * @return array
     */
    public function topQuestions($amount = 20)
    {
        $percentages = $this->getQuestionPercentages();
        uasort($percentages, function ($q1, $q2) {
            return $q1['percentage'] < $q2['percentage'];
        });

        return array_slice($percentages, 0, $amount, true);
    }

    /**
     * Returns the flop x questions
     * [question_id => ['title' => 'How much is the fish?", 'percentage' => 48], ... ].
     *
     * @param int $amount
     *
     * @return array
     */
    public function flopQuestions($amount = 20)
    {
        $percentages = $this->getQuestionPercentages();
        uasort($percentages, function ($q1, $q2) {
            return $q1['percentage'] > $q2['percentage'];
        });

        return array_slice($percentages, 0, $amount, true);
    }

    /**
     * Returns a history of the last played games.
     */
    public function gameStream()
    {
        return Game::where('app_id', $this->appId)
                   ->with('player1', 'player2')
                   ->orderBy('created_at', 'DESC')
                   ->take(20)
                   ->get();
    }

    /**
     * Returns an array of the amount of games played each day for the last week.
     *
     * @return array
     */
    public function latestGameCounts()
    {
        $games = Game::where('app_id', $this->appId)
                     ->where('created_at', '>=', date('Y-m-d', strtotime('-1 week')))
                     ->groupBy(DB::raw('DATE(created_at)'))
                     ->select('created_at', DB::raw('COUNT(*) as c'))
                     ->get();

        $data = [];
        // Make sure we have each day of the last week in the array
        $curDate = date('Y-m-d', strtotime('-1 week'));
        while ($curDate < date('Y-m-d')) {
            $data[date('d.m.Y', strtotime($curDate))] = 0;
            $curDate = date('Y-m-d', strtotime('+1 day', strtotime($curDate)));
        }
        // Set the counts for each day
        foreach ($games as $game) {
            $data[date('d.m.Y', strtotime($game->created_at))] = $game->c;
        }

        return $data;
    }

    /**
     * Returns a collection of players with their quiz stats.
     *
     * @return User[]
     */
    public function getQuizPlayersList()
    {
        return Cache::rememberForever('stats-player-list-'.$this->appId, function () {
            /** @var AppSettings $settings */
            $settings = new AppSettings($this->appId);

            // Get all players
            $players = User::where('app_id', $this->appId)
                           ->where('is_dummy', false)
                           ->where('is_api_user', false)
                           ->whereNull('deleted_at')
                           ->with(['metafields', 'tags'])
                           ->where(function ($query) {
                               $query->whereDoesntHave('tags', function ($tagQuery) {
                                   $tagQuery->where('hideHighscore', true);
                               });
                           })
                           ->get();

            $categories = Category::ofApp($this->appId)
                                  ->get();

            PlayerWrongAnswersByCategory::preload($this->appId);
            PlayerCorrectAnswersByCategory::preload($this->appId);
            PlayerGameCount::preload($this->appId);
            PlayerCorrectAnswers::preload($this->appId);
            PlayerCorrectAnswers::preload($this->appId);
            PlayerWrongAnswers::preload($this->appId);
            PlayerGameWins::preload($this->appId);
            PlayerGameLosses::preload($this->appId);
            PlayerGameDraws::preload($this->appId);
            PlayerGameAborts::preload($this->appId);

            // Add the stats to each player
            $players->map(function ($player) use ($categories, $settings) {
                // Fetch game specific stats
                $gameCount = (new PlayerGameCount($player->id))->noCache()->fetch();
                $gameWins = (new PlayerGameWins($player->id))->noCache()->fetch();
                $gameLosses = (new PlayerGameLosses($player->id))->noCache()->fetch();
                $gameDraws = (new PlayerGameDraws($player->id))->noCache()->fetch();
                $gameAborts = (new PlayerGameAborts($player->id))->noCache()->fetch();
                $gameWinPercentage = $gameWins > 0 ? $gameWins / ($gameWins + $gameDraws + $gameLosses) : 0;

                // Fetch question specific stats
                $answersCorrect = (new PlayerCorrectAnswers($player->id))->noCache()->fetch();
                $answersWrong = (new PlayerWrongAnswers($player->id))->noCache()->fetch();
                $answersCorrectPercentage = $answersCorrect > 0 ?
                        $answersCorrect / ($answersCorrect + $answersWrong) : 0;
                // Attach the stats to the player object
                $stats = [
                        'gameCount'         => $gameCount,
                        'gameWins'          => $gameWins,
                        'gameLosses'        => $gameLosses,
                        'gameDraws'         => $gameDraws,
                        'gameAborts'        => $gameAborts,
                        'gameWinPercentage' => $gameWinPercentage,

                        'answersCorrect'           => $answersCorrect,
                        'answersWrong'             => $answersWrong,
                        'answersCorrectPercentage' => $answersCorrectPercentage,
                        'tags' => $player->tags->implode('id', ','),
                        'categories' => [],
                ];

                // Add the stats by category
                foreach ($categories as $category) {
                    $answersCorrect = (new PlayerCorrectAnswersByCategory($player->id, $category->id))->noCache()->fetch();
                    $answersWrong = (new PlayerWrongAnswersByCategory($player->id, $category->id))->noCache()->fetch();

                    $answersCorrectPercentage = $answersCorrect > 0 ?
                            $answersCorrect / ($answersCorrect + $answersWrong) : 0;

                    $data = [
                            'name' => $category->name,
                            'answersCorrect'           => $answersCorrect,
                            'answersWrong'             => $answersWrong,
                            'answersCorrectPercentage' => $answersCorrectPercentage,
                    ];

                    $stats['categories'][$category->id] = $data;
                }

                $player->stats = $stats;
            });

            return $players;
        });
    }

    /**
     * Returns a collection of players with their training stats.
     *
     * @return User[]
     */
    public function getTrainingPlayersList()
    {
        return Cache::rememberForever('stats-training-player-list-'.$this->appId, function () {
            /** @var AppSettings $settings */
            $settings = new AppSettings($this->appId);

            // Get all players
            $players = User::where('app_id', $this->appId)
                           ->whereNull('deleted_at')
                           ->with(['metafields', 'tags'])
                           ->get();

            $categories = Category::ofApp($this->appId)
                                  ->get();

            PlayerQuestionBoxes::preload($this->appId);
            PlayerQuestionBoxesByCategory::preload($this->appId);

            // Add the stats to each player
            $players->map(function ($player) use ($categories, $settings) {
                $boxesCount = (new PlayerQuestionBoxes($player->id))->noCache()->fetch();
                $stats = [
                    'categories' => [],
                ];
                if (! $boxesCount || ! $boxesCount['total']) {
                    $stats['all'] = [
                        'average_box'   => 0,
                        'box_1_percent' => 0,
                        'box_2_percent' => 0,
                        'box_3_percent' => 0,
                        'box_4_percent' => 0,
                        'box_5_percent' => 0,
                        'box_1_total'   => 0,
                        'box_2_total'   => 0,
                        'box_3_total'   => 0,
                        'box_4_total'   => 0,
                        'box_5_total'   => 0,
                    ];
                } else {
                    $stats['all'] = [
                        'average_box' => (
                            (
                                (isset($boxesCount['box_1']) ? ($boxesCount['box_1'] * 1) : 0) +
                                (isset($boxesCount['box_2']) ? ($boxesCount['box_2'] * 2) : 0) +
                                (isset($boxesCount['box_3']) ? ($boxesCount['box_3'] * 3) : 0) +
                                (isset($boxesCount['box_4']) ? ($boxesCount['box_4'] * 4) : 0) +
                                (isset($boxesCount['box_5']) ? ($boxesCount['box_5'] * 5) : 0)
                            ) / $boxesCount['total']
                        ),
                        'box_1_percent' => (isset($boxesCount['box_1']) ? $boxesCount['box_1'] / $boxesCount['total'] : 0),
                        'box_2_percent' => (isset($boxesCount['box_2']) ? $boxesCount['box_2'] / $boxesCount['total'] : 0),
                        'box_3_percent' => (isset($boxesCount['box_3']) ? $boxesCount['box_3'] / $boxesCount['total'] : 0),
                        'box_4_percent' => (isset($boxesCount['box_4']) ? $boxesCount['box_4'] / $boxesCount['total'] : 0),
                        'box_5_percent' => (isset($boxesCount['box_5']) ? $boxesCount['box_5'] / $boxesCount['total'] : 0),
                        'box_1_total'   => (isset($boxesCount['box_1']) ? $boxesCount['box_1'] : 0),
                        'box_2_total'   => (isset($boxesCount['box_2']) ? $boxesCount['box_2'] : 0),
                        'box_3_total'   => (isset($boxesCount['box_3']) ? $boxesCount['box_3'] : 0),
                        'box_4_total'   => (isset($boxesCount['box_4']) ? $boxesCount['box_4'] : 0),
                        'box_5_total'   => (isset($boxesCount['box_5']) ? $boxesCount['box_5'] : 0),
                    ];
                }

                $stats['tags'] = $player->tags->implode('id', ',');

                // Add the stats by category
                foreach ($categories as $category) {
                    $boxesCount = (new PlayerQuestionBoxesByCategory($player->id, $category->id))->noCache()->fetch();
                    if (! $boxesCount || ! $boxesCount['total']) {
                        $stats['categories'][$category->id] = [
                            'name' => $category->name,
                            'average_box' => 0,
                            'box_1_percent' => 0,
                            'box_2_percent' => 0,
                            'box_3_percent' => 0,
                            'box_4_percent' => 0,
                            'box_5_percent' => 0,
                            'box_1_total'   => 0,
                            'box_2_total'   => 0,
                            'box_3_total'   => 0,
                            'box_4_total'   => 0,
                            'box_5_total'   => 0,
                        ];
                    } else {
                        $stats['categories'][$category->id] = [
                            'name' => $category->name,
                            'average_box' => (
                                (
                                    (isset($boxesCount['box_1']) ? ($boxesCount['box_1'] * 1) : 0) +
                                    (isset($boxesCount['box_2']) ? ($boxesCount['box_2'] * 2) : 0) +
                                    (isset($boxesCount['box_3']) ? ($boxesCount['box_3'] * 3) : 0) +
                                    (isset($boxesCount['box_4']) ? ($boxesCount['box_4'] * 4) : 0) +
                                    (isset($boxesCount['box_5']) ? ($boxesCount['box_5'] * 5) : 0)
                                ) / $boxesCount['total']
                            ),
                            'box_1_percent' => (isset($boxesCount['box_1']) ? $boxesCount['box_1'] / $boxesCount['total'] : 0),
                            'box_2_percent' => (isset($boxesCount['box_2']) ? $boxesCount['box_2'] / $boxesCount['total'] : 0),
                            'box_3_percent' => (isset($boxesCount['box_3']) ? $boxesCount['box_3'] / $boxesCount['total'] : 0),
                            'box_4_percent' => (isset($boxesCount['box_4']) ? $boxesCount['box_4'] / $boxesCount['total'] : 0),
                            'box_5_percent' => (isset($boxesCount['box_5']) ? $boxesCount['box_5'] / $boxesCount['total'] : 0),
                            'box_1_total'   => (isset($boxesCount['box_1']) ? $boxesCount['box_1'] : 0),
                            'box_2_total'   => (isset($boxesCount['box_2']) ? $boxesCount['box_2'] : 0),
                            'box_3_total'   => (isset($boxesCount['box_3']) ? $boxesCount['box_3'] : 0),
                            'box_4_total'   => (isset($boxesCount['box_4']) ? $boxesCount['box_4'] : 0),
                            'box_5_total'   => (isset($boxesCount['box_5']) ? $boxesCount['box_5'] : 0),
                        ];
                    }
                }

                $player->stats = $stats;
            });

            return $players;
        });
    }

    /**
     * Returns a collection of players which have the given tags or quiz teams with their stats.
     *
     * @param $tagIds
     * @param $categoryIds
     * @return User[]
     */
    public function getFilteredPlayersList($tagIds, $categoryIds)
    {
        /** @var AppSettings $settings */
        $settings = new AppSettings($this->appId);

        // Get all players
        $players = User::where('app_id', $this->appId)
            ->whereNull('deleted_at')
            ->with(['metafields', 'tags']);
        if ($tagIds) {
            $players->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('tags.id', $tagIds);
            });

        }
        $players = $players->get();

        $categories = Category::withTranslation()->ofApp($this->appId);
        if ($categoryIds) {
            $categories->whereIn('id', $categoryIds);
        }
        $categories = $categories->get();
        // Preparing the category names here, because we are accessing the name potentially hundreds of thousands of times
        // in the loop below which is really unperformant.
        $categoryNames = $categories->pluck('name', 'id');

        PlayerWrongAnswersByCategory::preload($this->appId);
        PlayerCorrectAnswersByCategory::preload($this->appId);
        PlayerGameCount::preload($this->appId);
        PlayerCorrectAnswers::preload($this->appId);
        PlayerCorrectAnswers::preload($this->appId);
        PlayerWrongAnswers::preload($this->appId);
        PlayerGameWins::preload($this->appId);
        PlayerGameLosses::preload($this->appId);
        PlayerGameDraws::preload($this->appId);
        PlayerGameAborts::preload($this->appId);

        // Add the stats to each player
        $players->map(function ($player) use ($categoryNames) {
            // Only access the player id once, as laravel accessors are expensive
            $playerId = $player->id;
            // Fetch game specific stats
            $gameCount = (new PlayerGameCount($playerId))->noCache()->fetch();
            $gameWins = (new PlayerGameWins($playerId))->noCache()->fetch();
            $gameLosses = (new PlayerGameLosses($playerId))->noCache()->fetch();
            $gameDraws = (new PlayerGameDraws($playerId))->noCache()->fetch();
            $gameAborts = (new PlayerGameAborts($playerId))->noCache()->fetch();
            $gameWinPercentage = $gameWins > 0 ? $gameWins / ($gameWins + $gameDraws + $gameLosses) : 0;

            // Fetch question specific stats
            $answersCorrect = (new PlayerCorrectAnswers($playerId))->noCache()->fetch();
            $answersWrong = (new PlayerWrongAnswers($playerId))->noCache()->fetch();
            $answersCorrectPercentage = $answersCorrect > 0 ?
                $answersCorrect / ($answersCorrect + $answersWrong) : 0;
            // Attach the stats to the player object
            $stats = [
                'gameCount'         => $gameCount,
                'gameWins'          => $gameWins,
                'gameLosses'        => $gameLosses,
                'gameDraws'         => $gameDraws,
                'gameAborts'        => $gameAborts,
                'gameWinPercentage' => $gameWinPercentage,

                'answersCorrect'           => $answersCorrect,
                'answersWrong'             => $answersWrong,
                'answersCorrectPercentage' => $answersCorrectPercentage,
                'tags' => $player->tags->implode('id', ','),
                'categories' => [],
            ];

            // Add the stats by category
            foreach ($categoryNames as $categoryId => $categoryName) {
                $answersCorrect = (new PlayerCorrectAnswersByCategory($playerId, $categoryId))->noCache()->fetch();
                $answersWrong = (new PlayerWrongAnswersByCategory($playerId, $categoryId))->noCache()->fetch();

                $answersCorrectPercentage = $answersCorrect > 0 ?
                    $answersCorrect / ($answersCorrect + $answersWrong) : 0;

                $data = [
                    'name' => $categoryName,
                    'answersCorrect'           => $answersCorrect,
                    'answersWrong'             => $answersWrong,
                    'answersCorrectPercentage' => $answersCorrectPercentage,
                ];

                $stats['categories'][$categoryId] = $data;
            }

            $player->stats = $stats;
        });

        return $players;
    }

    /**
     * Returns a list of questions with their statistics.
     *
     * @return Question[]|Collection
     */
    public function getQuestionList()
    {
        if(app()->runningInConsole()) {
            Config::set('app.force_language', defaultAppLanguage($this->appId));
        }
        $translationEngine = app(TranslationEngine::class);
        $questions = Question::withTranslation()->where('app_id', $this->appId)
            ->with(['category', 'category.categorygroup', 'app', 'questionDifficulties'])
            ->get();

        QuestionCorrect::preload($this->appId);
        QuestionWrong::preload($this->appId);

        $categories = $questions->pluck('category')->unique();
        $categories = $categories->filter(function ($value) { return !is_null($value); });

        $categoryGroups = $categories->pluck('categorygroup')->unique();
        $categoryGroups = $categoryGroups->filter(function ($value) { return !is_null($value); });

        $translationEngine->cacheTranslations($categoryGroups, null, $this->appId);
        $translationEngine->cacheTranslations($categories, null, $this->appId);
        $categoryGroups = $categoryGroups->keyBy('id');

        $categories->each(function ($category) use ($categoryGroups) {
            if($category->categorygroup_id) {
                $category->categorygroup = $categoryGroups->get($category->categorygroup_id);
            }
        });

        $categories = $categories->keyBy('id');

        $questions->map(function ($question) use ($categories) {
            if($question->category_id) {
                $question->category = $categories->get($question->category_id);
            }

            $correct = (new QuestionCorrect($question->id))->noCache()->fetch();
            $wrong = (new QuestionWrong($question->id))->noCache()->fetch();

            $question->stats = [
                'correct' => $correct,
                'wrong' => $wrong,
            ];

            $difficulty = $question->questionDifficulties->whereNull('user_id')->first();
            if($difficulty) {
                $question->difficulty = $difficulty->difficulty;
            } else {
                $question->difficulty = 0;
            }
        });

        return $questions;
    }

    /**
     * Returns a list of categories with their statistics.
     *
     * @return Category[]|Collection
     */
    public function getCategoryList()
    {
        $categories = Category::withTranslation()
            ->where('app_id', $this->appId)
            ->get();

        CategoryCorrect::preload($this->appId);
        CategoryWrong::preload($this->appId);

        $categories->map(function ($category) {
            $correct = (new CategoryCorrect($category->id))->noCache()->fetch();
            $wrong = (new CategoryWrong($category->id))->noCache()->fetch();

            $category->stats = [
                    'correct' => $correct,
                    'wrong'   => $wrong,
            ];
        });

        return $categories;
    }

    /**
     * Returns the quiz team stats.
     *
     * @return QuizTeam[]|Collection
     */
    public function getQuizTeamList()
    {
        return Cache::remember('quiz-team-stats-'.$this->appId, 60 * 60 * 28, function () {
            Log::info('Quiz Team API list for app '.$this->appId.' was not cached.');

            return $this->getRawQuizTeamList();
        });
    }

    /**
     * Returns the quiz team stats, circumventing cache.
     *
     * @return array
     */
    public function getRawQuizTeamList()
    {
        $categories = Category::ofApp($this->appId)->get();
        $quizTeams = QuizTeam::where('app_id', $this->appId)
            ->get();

        $quizTeams->map(function ($quizTeam) use ($categories) {
            $answersCorrect = (new QuizTeamCorrectAnswers($quizTeam->id))->noCache()->fetch();
            $answersWrong = (new QuizTeamWrongAnswers($quizTeam->id))->noCache()->fetch();
            $gameWins = (new QuizTeamGameWins($quizTeam->id))->noCache()->fetch();
            $games = (new QuizTeamGames($quizTeam->id))->noCache()->fetch();
            $points = null;

            $categoryAnswers = [];
            // Add the stats by category
            foreach ($categories as $category) {
                $categoryAnswers[$category->id] = (new QuizTeamCorrectAnswersByCategory($quizTeam->id, $category->id))->noCache()->fetch();
            }

            $percentage = $games > 0 ? round(($gameWins / $games) * 100, 2) : 0;
            $quizTeam->member_count = $quizTeam->members()->count();
            $quizTeam->stats = [
                'answersCorrect' => $answersCorrect,
                'answersWrong'   => $answersWrong,
                'gameWins'       => $gameWins,
                'points'         => $points,
                'games'          => $games,
                'categories'     => $categoryAnswers,
                'gameWinPercentage' => $percentage,
            ];
        });

        return $quizTeams;
    }

    /**
     * Returns the quiz teams stats in the format which the api needs.
     *
     * @return array
     */
    public function getQuizTeamsApiList()
    {
        return Cache::remember('quiz-team-api-stats-'.$this->appId, 60 * 60 * 24, function () {
            Log::info('Quiz Team API list for app '.$this->appId.' was not cached.');

            return $this->getRawQuizTeamApiList();
        });
    }

    /**
     * Returns the quiz team stats in the format which the api needs, circumventing cache.
     *
     * @return array
     */
    public function getRawQuizTeamApiList()
    {
        $quizTeams = $this->getRawQuizTeamList()
                        ->map(function ($quizTeam) {
                            $return = [
                                'id'                => $quizTeam->id,
                                'name'              => $quizTeam->name,
                                'answersCorrect'    => $quizTeam->stats['answersCorrect'],
                                'answersWrong'      => $quizTeam->stats['answersWrong'],
                                'gameWins'          => $quizTeam->stats['gameWins'],
                                'categories'        => $quizTeam->stats['categories'],
                                'gameWinPercentage' => $quizTeam->stats['gameWinPercentage'],
                                'member_count'      => $quizTeam->member_count,
                            ];
                            if (isset($quizTeam->stats['points'])) {
                                $return['points'] = $quizTeam->stats['points'];
                            }

                            return $return;
                        });

        $quizTeams = $quizTeams->sort(function ($a, $b) {
            if ($a['gameWinPercentage'] == $b['gameWinPercentage']) {
                if ($a['answersCorrect'] == $b['answersCorrect']) {
                    return 0;
                } else {
                    return $a['answersCorrect'] < $b['answersCorrect'] ? 1 : -1;
                }
            }

            return ($a['gameWinPercentage'] < $b['gameWinPercentage']) ? 1 : -1;
        });

        $i = 1;
        $quizTeams = $quizTeams->map(function ($result) use (&$i) {
            $result['position'] = $i++;

            return $result;
        });

        return array_values($quizTeams->toArray());
    }

    /**
     * Returns the stats for a single player.
     *
     * @param User $player
     *
     * @return array
     */
    public function getPlayer(User $player)
    {
        $categories = $player->getQuestionCategories();

        $points = (new PlayerPoints($player->id))->fetch();

        // Fetch game specific stats
        $gameCount = (new PlayerGameCount($player->id))->fetch();
        $gameWins = (new PlayerGameWins($player->id))->fetch();
        $gameLosses = (new PlayerGameLosses($player->id))->fetch();
        $gameDraws = (new PlayerGameDraws($player->id))->fetch();
        $gameAborts = (new PlayerGameAborts($player->id))->fetch();
        $gameWinPercentage = $gameWins > 0 ? $gameWins / ($gameWins + $gameDraws + $gameLosses) : 0;

        // Fetch question specific stats
        $answersCorrect = (new PlayerCorrectAnswers($player->id))->fetch();
        $answersWrong = (new PlayerWrongAnswers($player->id))->fetch();
        $answersCorrectPercentage = $answersCorrect > 0 ?
                $answersCorrect / ($answersCorrect + $answersWrong) : 0;

        $history = (new PlayerAnswerHistory($player->id))->fetch();

        // Attach the stats to the player object
        $stats = [
                'gameCount'         => $gameCount,
                'gameWins'          => $gameWins,
                'gameLosses'        => $gameLosses,
                'gameDraws'        => $gameDraws,
                'gameAborts'        => $gameAborts,
                'gameWinPercentage' => $gameWinPercentage,

                'answersCorrect'           => $answersCorrect,
                'answersWrong'             => $answersWrong,
                'answersCorrectPercentage' => $answersCorrectPercentage,

                'history' => $history,
                'points' => $points,

                'categories' => [],
        ];
        // Add the stats by category
        foreach ($categories as $category) {
            $answersCorrect = (new PlayerCorrectAnswersByCategory($player->id, $category->id))->fetch();
            $answersWrong = (new PlayerWrongAnswersByCategory($player->id, $category->id))->fetch();

            $answersCorrectPercentage = $answersCorrect > 0 ?
                    $answersCorrect / ($answersCorrect + $answersWrong) : 0;

            $data = [
                    'name' => $category->name,
                    'id' => $category->id,

                    'answersCorrect'           => $answersCorrect,
                    'answersWrong'             => $answersWrong,
                    'answersCorrectPercentage' => $answersCorrectPercentage,
            ];

            $stats['categories'][$category->id] = $data;
        }

        return $stats;
    }

    /**
     * Returns the stats for a single player.
     *
     * @param User $player
     *
     * @return array
     */
    public function getPlayerTraining(User $player)
    {
        $categories = $player->getQuestionCategories();

        // Fetch question specific stats
        $answersCorrect = (new PlayerTrainingCorrectAnswers($player->id))->fetch();
        $answersWrong = (new PlayerTrainingWrongAnswers($player->id))->fetch();
        $answersCorrectPercentage = $answersCorrect > 0 ?
                $answersCorrect / ($answersCorrect + $answersWrong) : 0;

        $history = (new PlayerTrainingAnswerHistory($player->id))->fetch();

        // Attach the stats to the player object
        $stats = [
                'answersCorrect'           => $answersCorrect,
                'answersWrong'             => $answersWrong,
                'answersCorrectPercentage' => $answersCorrectPercentage,

                'history' => $history,

                'categories' => [],
        ];
        // Add the stats by category
        foreach ($categories as $category) {
            $answersCorrect = (new PlayerTrainingCorrectAnswersByCategory($player->id, $category->id))->fetch();
            $answersWrong = (new PlayerTrainingWrongAnswersByCategory($player->id, $category->id))->fetch();

            $answersCorrectPercentage = $answersCorrect > 0 ?
                    $answersCorrect / ($answersCorrect + $answersWrong) : 0;

            $data = [
                    'name' => $category->name,

                    'answersCorrect'           => $answersCorrect,
                    'answersWrong'             => $answersWrong,
                    'answersCorrectPercentage' => $answersCorrectPercentage,
            ];

            $stats['categories'][$category->id] = $data;
        }

        return $stats;
    }

    /**
     * Gets the position of a player in the player statistics list, or -1 if he wasn't found.
     *
     * @return int
     */
    public function getAPIPlayerListPosition($user_id)
    {
        $player = (new Collection($this->getAPIPlayerList()))->first(function ($player) use ($user_id) {
            return $player['id'] == $user_id;
        });
        if (! $player) {
            return -1;
        }

        return $player['position'] ?: -1;
    }

    /**
     * Returns the player stats in the format which the api needs.
     *
     * @return array
     */
    public function getAPIPlayerList()
    {
        return Cache::remember('player-api-stats-'.$this->appId, 60 * 60 * 24, function () {
            Log::info('Api player list for app '.$this->appId.' was not cached.');

            return $this->getRawAPIPlayerList();
        });
    }

    /**
     * Returns the player stats in the format which the api needs, circumventing cache.
     *
     * @return array
     */
    public function getRawAPIPlayerList()
    {
        $app = App::find($this->appId);
        $appProfile = $app->getDefaultAppProfile();
        $results = [];

        $hideEmails = $appProfile->getValue('hide_emails_frontend');
        $categories = Category::ofApp($this->appId)->get();
        PlayerCorrectAnswersByCategory::preload($this->appId);
        PlayerCorrectAnswers::preload($this->appId);
        PlayerGameWins::preload($this->appId);
        PlayerGamesFinished::preload($this->appId);

        User::ofSameApp($this->appId)
            ->where('is_dummy', false)
            ->where('is_api_user', false)
            ->whereNull('deleted_at')
            ->where('tos_accepted', 1)
            ->where('active', 1)
            ->with(['metafields', 'tags'])
            ->where(function ($query) {
                $query->whereDoesntHave('tags', function ($tagQuery) {
                    $tagQuery->where('hideHighscore', true);
                });
            })
            ->get()
            ->map(function (User $player) use (&$results, $hideEmails, $categories) {
                $gameWins = (new PlayerGameWins($player->id))->noCache()->fetch();
                $gamesFinished = (new PlayerGamesFinished($player->id))->noCache()->fetch();
                $answersCorrect = (new PlayerCorrectAnswers($player->id))->noCache()->fetch();

                if ($hideEmails) {
                    $email = '';
                } else {
                    $email = $player->email;
                }

                $avatar = $player->avatar_url;

                $result = [
                    'id'             => $player->id,
                    'name'           => $player->displayname,
                    'image'          => $avatar,
                    'avatar_url'     => $avatar,
                    'email'          => $email,
                    'gameWins'       => $gameWins,
                    'gamesFinished'  => $gamesFinished,
                    'answersCorrect' => $answersCorrect,
                    'tags'           => $player->tags->pluck('id'),
                    'categories'     => [],
                ];

                // Add the stats by category
                foreach ($categories as $category) {
                    $answersCorrect = (new PlayerCorrectAnswersByCategory($player->id, $category->id))->noCache()->fetch();
                    $result['categories'][$category->id] = $answersCorrect;
                }

                $results[] = $result;
            });

        usort($results, function ($a, $b) {
            if ($a['gameWins'] == $b['gameWins']) {
                if ($a['answersCorrect'] == $b['answersCorrect']) {
                    return 0;
                }

                return ($a['answersCorrect'] < $b['answersCorrect']) ? 1 : -1;
            }

            return ($a['gameWins'] < $b['gameWins']) ? 1 : -1;
        });

        $i = 1;

        return array_map(function ($result) use (&$i) {
            $result['position'] = $i++;

            return $result;
        }, $results);
    }

    public function getRecentlyActiveUserCount($months = 6)
    {
        $app = App::findOrFail($this->appId);
        $date = Carbon::now()->subMonths($months);
        $player1IdsFromGames = Game::where('app_id', $this->appId)
            ->where('created_at', '>=', $date)
            ->groupBy('player1_id')
            ->pluck('player1_id');
        $player2IdsFromGames = Game::where('app_id', $this->appId)
            ->where('created_at', '>=', $date)
            ->groupBy('player2_id')
            ->pluck('player2_id');
        $usersFromViewcounts = AnalyticsEvent
            ::where('foreign_id', $this->appId)
            ->where('foreign_type', $app->getMorphClass())
            ->where('type', AnalyticsEvent::TYPE_VIEW_HOME)
            ->where('created_at', '>=', $date)
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->pluck('user_id');
        $botIds = User::where('app_id', $this->appId)
            ->withoutGlobalScope('human')
            ->where('is_bot', '>', 0)
            ->pluck('id');

        return $player1IdsFromGames
            ->merge($player2IdsFromGames)
            ->merge($usersFromViewcounts)
            ->unique()
            ->filter(function ($userId) use ($botIds) {
                return ! $botIds->contains($userId);
            })
            ->count();
    }

    public function runningGames()
    {
        return Game::where('status', '>', Game::STATUS_FINISHED)
            ->where('app_id', $this->appId)
            ->count();
    }

    public function startedGamePlayers() {
        return Game::where('app_id', $this->appId)
            ->whereHas('player1', function ($query) {
                return $query->whereNull('deleted_at')->where('active', 1);
            })
            ->groupBy('player1_id')
            ->get()
            ->count();
    }
}
