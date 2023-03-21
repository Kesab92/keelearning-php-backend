<?php

namespace App\Stats;

use App\Models\GameQuestionAnswer;
use App\Models\User;
use DB;

/**
 * Calculates the amount of answers the user answered correctly for a category, excluding bot games.
 */
class PlayerCorrectAnswersByCategoryBetweenDates extends Statistic
{
    /**
     * @var
     */
    private $playerId;
    /**
     * @var
     */
    private $categoryId;
    /**
     * @var
     */
    private $from;
    /**
     * @var
     */
    private $to;
    private static $_preloadData;

    public function __construct($playerId, $categoryId, $from, $to)
    {
        $this->playerId = $playerId;
        $this->categoryId = $categoryId;
        if (! $this->categoryId) {
            $this->categoryId = 0;
        }
        $this->from = $from;
        $this->to = $to;
    }

    public static function preload($appId, $from, $to, $categoryId = null)
    {
        self::$_preloadData = [];
        $botIds = User::where('app_id', $appId)
            ->withoutGlobalScope('human')
            ->where('is_bot', '>', 0)
            ->pluck('id');
        if (! $categoryId) {
            $categoryId = 0;
        }
        $data = GameQuestionAnswer::join('users', 'users.id', '=', 'game_question_answers.user_id')
            ->join('game_questions', 'game_questions.id', '=', 'game_question_answers.game_question_id')
            ->join('questions', 'questions.id', '=', 'game_questions.question_id')
            ->join('game_rounds', 'game_rounds.id', '=', 'game_questions.game_round_id')
            ->join('games', 'games.id', '=', 'game_rounds.game_id')
            ->whereNotIn('games.player1_id', $botIds)
            ->whereNotIn('games.player2_id', $botIds)
            ->whereBetween('game_question_answers.created_at', [$from, $to])
            ->where('users.app_id', $appId)
            ->where('game_question_answers.result', 1)
            ->select(DB::RAW('COUNT(*) as count, game_question_answers.user_id as user_id'));
        if ($categoryId) {
            $data->where('questions.category_id', $categoryId)
                ->addSelect('questions.category_id')
                ->groupBy(['game_question_answers.user_id', 'questions.category_id']);
        } else {
            $data->groupBy(['game_question_answers.user_id']);
        }
        $data = $data
            ->getQuery()
            ->get();
        foreach ($data as $entry) {
            if (! isset(self::$_preloadData[$categoryId])) {
                self::$_preloadData[$categoryId] = [];
            }
            self::$_preloadData[$categoryId][$entry->user_id] = $entry->count;
        }
    }

    /**
     * Returns the amount of answers the user answered correctly.
     *
     * @return int
     */
    protected function getValue()
    {
        if (self::$_preloadData) {
            return isset(self::$_preloadData[$this->categoryId][$this->playerId]) && isset(self::$_preloadData[$this->categoryId][$this->playerId]) ? self::$_preloadData[$this->categoryId][$this->playerId] : 0;
        }
        $correctAnswers = GameQuestionAnswer::ofUser($this->playerId)
            ->where('result', 1)
            ->whereBetween('created_at', [$this->from, $this->to])
            ->whereHas('gameQuestion.gameRound.game.player1', function ($query) {
                $query->where('is_bot', 0);
            })
            ->whereHas('gameQuestion.gameRound.game.player2', function ($query) {
                $query->where('is_bot', 0);
            });
        if ($this->categoryId) {
            $correctAnswers = $correctAnswers->whereHas('gameQuestion.question', function ($q) {
                $q->where('questions.category_id', $this->categoryId);
            });
        }

        return $correctAnswers->count();
    }

    protected function getCacheKey()
    {
        return 'player-correct-answers-'.$this->playerId.'-category-'.($this->categoryId ?: 'all').'-'.$this->from->timestamp.'-'.$this->to->timestamp;
    }

    protected function getCacheTags()
    {
        return ['player-'.$this->playerId];
    }
}
