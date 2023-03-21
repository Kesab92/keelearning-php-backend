<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use App\Models\User;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserDelete;
use App\Services\UserAnonymisation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RemoveExpiredUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes expired users';

    private $ua;

    /**
     * Create a new command instance.
     *
     * @param UserAnonymisation $ua
     */
    public function __construct(UserAnonymisation $ua)
    {
        $this->ua = $ua;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        $this->info('Start deleting expired users');
        $today = Carbon::now();
        /** @var AccessLogEngine $accessLogEngine */
        $accessLogEngine = app(AccessLogEngine::class);

        $users = User::whereNotNull('expires_at')
            ->withoutMainAdmins()
            ->where('expires_at', '>', '2020-01-01')
            ->where('expires_at', '<=', $today)
            ->limit(100)
            ->get();

        foreach ($users as $user) {
            $this->info('Remove user: '.$user->id);
            if ($this->ua->anonymiseUser($user)) {
                $accessLogEngine->log(AccessLog::ACTION_USER_DELETE, new AccessLogUserDelete($user), 0);
            }
        }

        $this->info('Finished removing expired users');
    }
}
