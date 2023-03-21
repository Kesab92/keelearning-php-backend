<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\User;
use App\Services\AppSettings;
use App\Services\Bots\BotFactory;
use App\Services\GameEngine;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LetBotPlayGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Game
     */
    protected $game = null;

    /**
     * @var User
     */
    protected $bot = null;

    /**
     * @var User
     */
    protected $opponent = null;

    /**
     * @var GameEngine|null
     */
    protected $gameEngine = null;

    /**
     * @var null
     */
    protected $botFactory = null;

    /**
     * Number of retries before job failed.
     * @var int
     */
    public $tries = 0;

    /**
     * @var AppSettings
     */
    private $settings;

    /**
     * CreateBot constructor.
     * @param $game
     * @param $bot
     * @param $opponent
     */
    public function __construct($game, $bot, $opponent)
    {
        $this->gameEngine = new GameEngine();
        $this->botFactory = new BotFactory();
        $this->game = $game;
        $this->bot = $bot;
        $this->opponent = $opponent;
        $this->settings = new AppSettings($this->game->app_id);
        $this->queue = QueuePriority::HIGH;
    }

    /**
     *  Handles the bot functionality.
     */
    public function handle()
    {
        \Log::info('Starting bot for game #'.$this->game->id);

        // Abort if game is finished already
        if ($this->game->status === Game::STATUS_FINISHED || $this->game->status === Game::STATUS_CANCELED) {
            return;
        }

        // Create bot with given difficulty
        $bot = $this->botFactory->createBot($this->bot, $this->opponent);

        // Get Game Round Questions
        $round = $this->game->getCurrentRound();
        if (! $round) {
            return;
        }

        // If no category is chosen yet
        if ($round->category_id == null) {
            $this->selectCategory($round);
        }

        $gameQuestions = $this->game
            ->gameQuestions()
            ->where('game_round_id', $round->id)
            ->get();

        foreach ($gameQuestions as $gameQuestion) {
            \Log::info('Processing game question #'.$gameQuestion->id);
            // Calculate correct or incorrect answer
            $correct = $bot->process($gameQuestion);

            // Anwswer
            $bot->answer($gameQuestion, $correct);
            $this->reloadGame();
            $this->gameEngine->sendNewGameSync($this->game);

            // sleep for 1-2 seconds before answering the next question
            usleep(rand(100, 200) * 10000);
        }

        // End round
        if (! $round->isFinishedFor($this->bot->id)) {
            $this->game->status = Game::STATUS_CANCELED;
            $this->game->save();
            throw new \Exception('BOT: error finishing game #'.$this->game->id);
        }

        $this->reloadGame();
        $this->game->finishPlayerRound();

        $this->gameEngine->sendNewGameSync($this->game);
    }

    private function reloadGame()
    {
        $this->game->refresh();
    }

    private function selectCategory($round)
    {
        if ($this->settings->getValue('use_subcategory_system')) {
            $gameCategorygroups = $this->gameEngine->getAvailableCategorygroups($this->game, $round->id);
            $categories = [];
            foreach ($gameCategorygroups as $gameCategorygroup) {
                foreach ($gameCategorygroup['categories'] as $category) {
                    $categories[] = $category;
                }
            }
        } else {
            $categories = $this->gameEngine->getAvailableCategories($this->game, $round->id);
        }
        if (! $categories) {
            $this->game->status = Game::STATUS_CANCELED;
            $this->game->save();
            throw new \Exception('BOT: No categories found for game #'.$this->game->id);
        }

        $selectedCategory = collect($categories)->random();
        $this->gameEngine->setNextCategory($round, $selectedCategory['id']);
    }
}
