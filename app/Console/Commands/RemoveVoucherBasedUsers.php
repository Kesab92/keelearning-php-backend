<?php

namespace App\Console\Commands;

use App\Models\AccessLog;
use App\Models\User;
use App\Models\VoucherCode;
use App\Services\AccessLogEngine;
use App\Services\AccessLogMeta\AccessLogUserDelete;
use App\Services\UserAnonymisation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RemoveVoucherBasedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voucher-users:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes users from app which registered by a voucher';

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
        $this->info('Start deleting invalid users');

        $today = Carbon::now();
        $voucherCodes = VoucherCode::whereNotNull('user_id')
            ->where('user_id', '!=', -1)
            ->get();

        /** @var AccessLogEngine $accessLogEngine */
        $accessLogEngine = app(AccessLogEngine::class);

        $userIds = $voucherCodes->map(function ($item) {
            return $item->user_id;
        })->unique();

        $users = User
            ::whereIn('users.id', $userIds)
            ->withoutMainAdmins()
            ->whereNull('expires_at')
            ->get();
        foreach ($users as $user) {
            $removeUser = true;
            /** @var VoucherCode[] $userVoucherCodes */
            $userVoucherCodes = $voucherCodes->filter(function ($item) use ($user) {
                return $item->user_id == $user->id;
            });
            foreach ($userVoucherCodes as $voucherCode) {
                if (!$voucherCode->voucher->validity_duration) {
                    $removeUser = false;
                    break;
                }

                $expiryDate = $voucherCode->getEndDate();
                if (!$expiryDate || $expiryDate > $today) {
                    $removeUser = false;
                    break;
                }
            }

            if ($removeUser && ! $user->is_admin) {
                $this->info('Remove user: '.$user->id);
                if ($this->ua->anonymiseUser($user)) {
                    $accessLogEngine->log(AccessLog::ACTION_USER_DELETE, new AccessLogUserDelete($user), 0);
                }
            }
        }

        $this->info('Finished clean up of invalid users');
    }
}
