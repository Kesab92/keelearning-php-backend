<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\App;
use App\Models\User;
use App\Services\GameEngine;
use Illuminate\Console\Command;

class UsageReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    private $gameEngine;
    private $mailer;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send e-mails to those players, that were inactive for more than 5 days';

    /**
     * Create a new command instance.
     *
     * @param Mailer $mailer
     * @param GameEngine $gameEngine
     */
    public function __construct(Mailer $mailer, GameEngine $gameEngine)
    {
        parent::__construct();

        $this->mailer = $mailer;
        $this->gameEngine = $gameEngine;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $apps = App::all();

        /** @var App $app */
        foreach ($apps as $app) {
            // All users that have played at least one game
            $users = User::where('users.app_id', $app->id)
                ->whereNull('deleted_at')
                ->active()
                ->where('tos_accepted', 1)
                ->whereRaw('id IN (SELECT player1_id as player FROM games WHERE app_id = '.$app->id.' UNION DISTINCT SELECT player2_id as player FROM games WHERE app_id = '.$app->id.')');

            $count = $users->count();

            $this->info('Checking AppReminders for '.$count.' users of app #'.$app->id);
            $bar = $this->output->createProgressBar($count);

            $users->chunk(1000, function ($users) use ($bar) {
                /** @var User $user */
                foreach ($users as $user) {
                    // If the user has been idle for too long, send a reminder mail
                    \App\Services\UsageReminder::seekAndRemindUser($user, $this->gameEngine, $this->mailer, $user->app_id, $user->id);
                    $bar->advance();
                }
            });
            $bar->finish();
            $this->line('');
        }
    }
}
