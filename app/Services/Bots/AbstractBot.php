<?php

namespace App\Services\Bots;

use App\Models\GameQuestion;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Services\GameEngine;

abstract class AbstractBot
{
    /**
     * @var null
     */
    protected $gameEngine = null;

    /**
     * @var null
     */
    protected $botId = null;

    /**
     * @var null
     */
    protected $opponentId = null;

    /**
     * AbstractBot constructor.
     * @param $botId
     * @param $opponentId
     */
    public function __construct($botId, $opponentId)
    {
        $this->gameEngine = new GameEngine();
        $this->opponentId = $opponentId;
        $this->botId = $botId;
    }

    /**
     * Determine the correct/incorrect answer.
     * @param $gameQuestion
     * @return mixed
     */
    abstract public function process(GameQuestion $gameQuestion);

    /**
     * Submits answer to given game question.
     * @param $gameQuestion
     * @param $correctAnswer
     */
    public function answer($gameQuestion, $correctAnswer)
    {
        $questionAnswer = QuestionAnswer::where('question_id', $gameQuestion->question_id)
            ->where('correct', $correctAnswer)
            ->get();

        $gameQuestionAnswer = $this->gameEngine->createEmptyGameQuestionAnswer($gameQuestion, $this->botId);
        $answer = null;
        if ($gameQuestion->question->type === Question::TYPE_MULTIPLE_CHOICE) {
            $answer = $questionAnswer->pluck('id')->toArray();
        } elseif ($gameQuestion->question->type === Question::TYPE_SINGLE_CHOICE
            || $gameQuestion->question->type === Question::TYPE_BOOLEAN) {
            $answer = $questionAnswer->first()->id;
        }

        $this->gameEngine->updateEmptyGameQuestionAnswer($gameQuestionAnswer, $answer);
    }

    /**
     * @param GameQuestion $gameQuestion
     * @return int
     */
    public function calculateAnswerCount(GameQuestion $gameQuestion)
    {
        if (! $gameQuestion->question) {
            $gameQuestion->gameRound->game->status = Game::STATUS_CANCELED;
            $gameQuestion->gameRound->game->save();
            throw new \Exception('BOT: no question assigned to gameQuestion #'.$gameQuestion->id);
        }
        $answerCount = QuestionAnswer::where('question_id', $gameQuestion->question_id)->count();
        if ($gameQuestion->question->type === Question::TYPE_MULTIPLE_CHOICE) {
            $sum = 0;
            for ($i = 1; $i <= $answerCount; $i++) {
                $sum += $this->calculateCombination($i, $answerCount);
            }
            $answerCount = $sum;
        }

        return $answerCount;
    }

    /**
     * Calculates the combination - $answerCount out of $count.
     *
     * @param $count
     * @param $answerCount
     * @return float|int
     */
    private function calculateCombination($count, $answerCount)
    {
        return $this->factorial($answerCount) / ($this->factorial($answerCount - $count) * $this->factorial($count));
    }

    /**
     * Calculates the factorial number. FIXME: (Theres no php native impl).
     * @param $number
     * @return float|int
     */
    private function factorial($number)
    {
        $factorial = 1;
        for ($i = 1; $i <= $number; $i++) {
            $factorial = $factorial * $i;
        }

        return $factorial;
    }
}
