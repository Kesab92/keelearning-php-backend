<?php

namespace App\Console\Commands;

use App\Models\App;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateApiTokenForApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apitoken:create {appId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an API token for the app';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $appId = $this->argument('appId');
        App::findOrFail($appId);

        $user = User::where('app_id', $appId)
            ->where('is_api_user', true)
            ->first();

        if(!$user) {
            $password = randomPassword();

            $user = new User();
            $user->app_id = $appId;
            $user->is_api_user = true;
            $user->username = 'API';
            $user->email = 'api@keelearning.de';
            $user->password = Hash::make($password);

            $user->save();
        }

        $token = $user->createToken($user->app_id);

        $this->info($token->plainTextToken);

        return 0;
    }
}
