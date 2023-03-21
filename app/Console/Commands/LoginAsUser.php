<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AppConfigEngine;
use Illuminate\Console\Command;

class LoginAsUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:login-as {userId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Returns the info needed to login as a certain user.';

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
        $userId = $this->argument('userId');
        $user = User::findOrFail($userId);
        $appProfile = $user->getAppProfile();
        $config = (new AppConfigEngine)->getConfig('id:'.$appProfile->id, $user);
        $token = auth('api')->login($user);

        $authInfo = [
            'browserLocaleDetected' => true,
            'config' => $config,
            'locale' => $user->getLanguage(),
            'localeManuallySelected' => true,
            'token' => $token,
            'user' => [
                'active' => $user->active,
                'avatar' => $user->avatar_url,
                'displayname' => $user->displayname,
                'email' => $user->email,
                'id' => $user->id,
                'is_admin' => $user->is_admin,
                'name' => $user->username,
                'tmpAccount' => $user->isTmpAccount(),
                'tos_accepted' => $user->tos_accepted,
            ],
        ];

            $this->line('Only use this command on staging / locally or on prod with keeunit users.');
        $this->line('Go here:');
        $this->line($appProfile->app_hosted_at);
        $this->line('');
        $this->line('Copy this into your console:');
        $this->line('localStorage.clear();localStorage.setItem(\'vuex-persist-auth\', \''.addslashes(json_encode($authInfo)).'\');window.location = \'/\'');
    }
}
