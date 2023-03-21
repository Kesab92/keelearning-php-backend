<?php

namespace App\Services\Bots;

use App\Models\User;

class BotFactory
{
    /**
     * Username of the 'easy' bot.
     */
    const EASY_BOT = 1;

    /**
     * Username of the 'medium' bot.
     */
    const MEDIUM_BOT = 2;

    /**
     * Creates a bot which handles all communication between question and game round.
     * It creates a bot with difficulty based on user.
     * @param User $user
     * @param User $opponent
     * @return EasyBot|MediumBot
     * @throws \Exception
     */
    public function createBot(User $user, User $opponent)
    {
        if (! $user->is_bot) {
            throw new \Exception('User is not a bot');
        }

        switch ($user->is_bot) {
            case self::EASY_BOT:
                return new EasyBot($user->id);
            case self::MEDIUM_BOT:
                return new MediumBot($user->id, $opponent->id);
        }
    }
}
