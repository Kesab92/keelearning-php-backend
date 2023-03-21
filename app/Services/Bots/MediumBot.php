<?php

namespace App\Services\Bots;

use App\Models\Game;
use App\Models\GameQuestion;

class MediumBot extends AbstractBot
{
    /**
     * This value checks the last x games which should be included in the calculation of the win percentage.
     * @var int
     */
    protected $lastGameCount = 5;

    /**
     * This value increases the probability by which the bot answers correctly.
     * @var float
     */
    protected $correctionValue = 0.1;

    /**
     * MediumBot constructor.
     * @param $botId
     * @param $opponentId
     */
    public function __construct($botId, $opponentId)
    {
        parent::__construct($botId, $opponentId);
    }

    /**
     * Determine the correct/incorrect answer.
     * @param $gameQuestion
     * @return bool
     */
    public function process(GameQuestion $gameQuestion)
    {
        $answerCount = $this->calculateAnswerCount($gameQuestion);
        $randomAnswerProbability = (1 / $answerCount);

        $averageDifficulty = $gameQuestion->question
            ->questionDifficulties()
            ->where('user_id', $this->opponentId)
            ->value('difficulty');

        if ($averageDifficulty == null) {
            $averageDifficulty = $gameQuestion->question
                ->questionDifficulties()
                ->whereNull('user_id')
                ->value('difficulty');
        }

        $games = Game::where(function ($query) {
            $query->where('player1_id', $this->opponentId)
                    ->orWhere('player2_id', $this->opponentId);
        })
            ->where('status', Game::STATUS_FINISHED)
            ->limit($this->lastGameCount)
            ->get();

        if ($games->count() < 5) {
            $gamesWonPercentage = 0.5;
        } else {
            $gamesWon = $games->filter(function ($item) {
                return $item->winner === $this->opponentId;
            })->count();
            $gamesWonPercentage = $gamesWon / $games->count();
        }

        // Normalize the difficulty (from -1 - 1 to 0 - 1). A higher number means that the question is easier.
        $questionDifficultyFactor = ($averageDifficulty + 1) / 2;

        // We answer at least as good as we would answer randomly
        $isCorrectProbability = $randomAnswerProbability;
        // Add more probability based on how easy the question is and how often the user won games recently
        $isCorrectProbability += (1 - $randomAnswerProbability) * $questionDifficultyFactor * $gamesWonPercentage;
        // Add some more probability, because the bot should be slightly better than the user
        $isCorrectProbability += $this->correctionValue;
        \Log::info('question difficulty: '.$questionDifficultyFactor.' gamesWonPercentage: '.$gamesWonPercentage.' randomPercentage: '.$randomAnswerProbability.' finalProbability: '.$isCorrectProbability);

        $threshold = rand(0, 99) / 100;
        if ($isCorrectProbability > $threshold) {
            return true;
        } else {
            return false;
        }
    }
}
