<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BotCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:bots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates bots for all apps, if they don\'t exist already';

    /**
     * Array of bot names.
     * @var array
     */
    public static $names = [
        'Robi Roboter' => 1,
        'Medi Medium' => 2,
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  Creates all possible bots for all apps.
     */
    public function handle()
    {
        foreach (App::all() as $app) {
            $this->info('Checking app for bots: '.$app->name);
            foreach (self::$names as $botName => $difficulty) {
                $user = User::bot()->where('app_id', $app->id)
                    ->where('is_bot', $difficulty)
                    ->first();

                $this->info($botName.($user ? ' found' : ' not found'));
                if (! $user) {
                    $user = new User();
                    $user->app_id = $app->id;
                    $user->username = $botName;
                    $user->email = 'bot-'.$difficulty.'@keelearning.de';
                    $user->password = Hash::make(Str::random(50));
                    $user->tos_accepted = true;
                    $user->is_admin = false;
                    $user->is_bot = $difficulty;
                    $user->save();
                }
            }
        }
    }
}
