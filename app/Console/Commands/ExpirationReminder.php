<?php

namespace App\Console\Commands;

use App\Mail\Mailer;
use App\Models\User;
use App\Models\App;
use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Services\UserEngine;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class ExpirationReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:remindexpiration';

    private $mailer;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remind users about their expiring accounts';

    /**
     * Create a new command instance.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // we have to catch both:
        // users to be deleted via `expires_at`
        // as well as an assigned voucher with `validity_duration` date
        /*
            select
                users.*,
                IF(
                    expires_at IS NOT NULL,
                    expires_at,
                    MAX(
                        IF(
                            `vouchers`.`validity_interval` = 0,
                            DATE_FORMAT( DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` MONTH),"%Y-%m-%d"),
                            DATE_FORMAT( DATE_ADD(`voucher_codes`.`cash_in_date`, INTERVAL `vouchers`.`validity_duration` DAY),"%Y-%m-%d")          )
                    )
                ) AS expires_at_combined
            from
                `users`
            left join `voucher_codes` on
                `voucher_codes`.`user_id` = `users`.`id`
            left join `vouchers` on
                `vouchers`.`id` = `voucher_codes`.`voucher_id`
            where
                `users`.`deleted_at` is null
                and `users`.`app_id` = ?
                and ((`vouchers`.`validity_duration` > 0
                    and `users`.`expires_at` is null)
                or `users`.`expires_at` is not null)
                and `is_bot` = 0
            group by
                `users`.`id`

        */

        $userEngine = app(UserEngine::class);

        $allApps = App::get();
        $deletionInformationByAppProfile = [];
        $changedDeletionDays = AppProfileSetting::where('key', 'days_before_user_deletion')
            ->pluck('value', 'app_profile_id');
        foreach (AppProfile::all() as $appProfile) {
            $daysBefore = $changedDeletionDays->get($appProfile->id, AppProfileSetting::DAYS_BEFORE_USER_DELETION);
            $deletionInformationByAppProfile[$appProfile->id] = [
                'days_before' => $daysBefore,
                'deletion_date' => Carbon::now()->addDays($daysBefore)->toDateString(),
            ];
        }
        foreach ($allApps as $app) {
            $userEngine
                ->getUsersWithCombinedExpiresAtQuery($app->id)
                ->with('tags')
                ->chunk(1000, function ($users) use ($deletionInformationByAppProfile) {
                    foreach ($users as $user) {
                        $userAppProfile = $user->getAppProfile();
                        $deletionInformation = $deletionInformationByAppProfile[$userAppProfile->id];
                        if ($user->expires_at_combined === $deletionInformation['deletion_date']) {
                            $this->sendExpirationReminder($user, $deletionInformation['days_before']);
                        }
                    }
                });
        }
    }

    function sendExpirationReminder(User $user, $daysBefore)
    {
        if (!$user->isMaillessAccount()) {
            $this->mailer->sendExpirationReminder($user, $daysBefore);
        }
    }
}

