<?php

namespace App\Services\Bots;

use App\Models\GameQuestion;

class EasyBot extends AbstractBot
{
    /**
     * This value increases the answer "result".
     * @var float
     */
    protected $correctionValue = 0.1;

    /**
     * EasyBot constructor.
     * @param $botId
     */
    public function __construct($botId)
    {
        parent::__construct($botId, null);
    }

    /**
     * Determine the correct/incorrect answer.
     * @param GameQuestion $gameQuestion
     * @return mixed
     */
    public function process(GameQuestion $gameQuestion)
    {
        $answerCount = $this->calculateAnswerCount($gameQuestion);
        $answerResult = (1 / $answerCount) + $this->correctionValue;
        $threshold = rand(0, 99) / 100;

        if ($answerResult < $threshold) {
            return true;
        } else {
            return false;
        }
    }
}
