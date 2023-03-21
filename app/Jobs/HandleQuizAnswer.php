<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\User;
use App\Services\GameEngine;
use App\Services\QuestionDifficultyEngine;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleQuizAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Game
     */
    protected $game = null;

    /**
     * Number of retries before job failed.
     * @var int
     */
    public $tries = 1;
    /**
     * @var GameEngine
     */
    private GameEngine $gameEngine;
    private $wasWithinTime;
    /**
     * @var QuestionDifficultyEngine|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $questionDifficultyEngine;
    private GameQuestion $gameQuestion;
    private User $user;
    private GameQuestionAnswer $gameQuestionAnswer;

    /**
     * CreateBot constructor.
     * @param $game
     * @param $wasWithinTime
     */
    public function __construct($game, $wasWithinTime, GameQuestion $gameQuestion, User $user, GameQuestionAnswer $gameQuestionAnswer)
    {
        $this->gameEngine = app(GameEngine::class);
        $this->questionDifficultyEngine = app(QuestionDifficultyEngine::class);
        $this->game = $game;
        $this->wasWithinTime = $wasWithinTime;
        $this->gameQuestion = $gameQuestion;
        $this->user = $user;
        $this->gameQuestionAnswer = $gameQuestionAnswer;
    }

    /**
     *  Handles the bot functionality.
     */
    public function handle()
    {
        $this->gameEngine->sendNewGameSync($this->game);

        // we do not want users falling asleep to calculate into question difficulty
        if ($this->wasWithinTime) {
            $this->questionDifficultyEngine->updateDifficulty(
                $this->gameQuestion->question,
                $this->user,
                $this->gameQuestionAnswer->result,
                Carbon::parse($this->gameQuestionAnswer->created_at)->diffInSeconds()
            );
        }

        // Clear the cache for this user
        $this->user->clearStatsCache();
    }

}
