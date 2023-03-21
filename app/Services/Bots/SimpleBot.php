<?php

namespace App\Services\Bots;

use App\Models\GameQuestion;

class SimpleBot extends AbstractBot
{
    /**
     * SimpleBot constructor.
     * @param $botId
     */
    public function __construct($botId)
    {
        parent::__construct($botId);
    }

    /**
     * Returns always the correct answer.
     * @param GameQuestion $gameQuestion
     * @return bool|mixed
     */
    public function process(GameQuestion $gameQuestion)
    {
        return true;
    }
}
