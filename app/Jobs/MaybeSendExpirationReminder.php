<?php

namespace App\Jobs;

use App\Mail\Mailer;
use App\Models\AppProfileSetting;
use App\Models\User;
use App\Models\VoucherCode;
use App\Services\AppProfileSettings;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class MaybeSendExpirationReminder
 * @package App\Jobs
 *
 * This job is dispatched when a user redeems a new voucher, and we check if the voucher limits the user's access to the
 * app and if they have < 10 days of access left. If that's the case, we send them an email to warn them about their
 * account deletion.
 */
class MaybeSendExpirationReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    /**
     * @var User
     */
    private User $user;
    /**
     * @var Mailer
     */
    private Mailer $mailer;

    /**
     * CreateBot constructor.
     * @param User $user
     * @param Mailer $mailer
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->mailer = app(Mailer::class);
    }

    /**
     *  Handles the bot functionality.
     */
    public function handle()
    {
        if($this->user->is_admin || $this->user->isMaillessAccount() || $this->user->isTmpAccount()) {
            return;
        }
        $notifyUser = true;
        /** @var VoucherCode[] $userVoucherCodes */
        $userVoucherCodes = VoucherCode::where('user_id', $this->user->id)->get();
        if(!$userVoucherCodes->count()) {
            return;
        }

        $appProfile = $this->user->getAppProfile();
        $appProfileSettings = new AppProfileSettings($appProfile->id);
        $daysBefore = (int)$appProfileSettings->getValue('days_before_user_deletion') ?: AppProfileSetting::DAYS_BEFORE_USER_DELETION;

        $referenceDate = Carbon::now()->addDays($daysBefore);
        $deletionDate = null;
        foreach ($userVoucherCodes as $voucherCode) {
            if (!$voucherCode->voucher->validity_duration) {
                $notifyUser = false;
                break;
            }

            $expiryDate = $voucherCode->getEndDate();
            if (!$expiryDate || $expiryDate > $referenceDate) {
                $notifyUser = false;
                break;
            }

            if($deletionDate === null || $expiryDate->isBefore($deletionDate)) {
                $deletionDate = $expiryDate;
            }
        }

        if ($notifyUser && $deletionDate) {
            $this->mailer->sendExpirationReminder($this->user, $deletionDate->diffInDays(Carbon::now()));
        }
    }

}
