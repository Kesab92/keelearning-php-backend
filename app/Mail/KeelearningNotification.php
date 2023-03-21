<?php

namespace App\Mail;

use App\Models\App;
use App\Models\AppProfile;
use App\Models\User;
use App\Models\UserNotificationSetting;
use App\Push\Notifier;
use App\Services\AppProfiles\AppProfileCache;
use App\Services\AppSettings;
use Illuminate\Container\Container;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailServiceProvider;
use Mail;
use ReflectionException;

/**
 * This class makes sure that the emails get sent via the specified custom smtp config
 * If no custom config is set, the default mail config is used.
 *
 * Class KeelearningMail
 */
abstract class KeelearningNotification extends Mailable
{

    public function __construct()
    {
        $this->clearAppProfileCaches();
    }

    /**
     * @var bool Notifications with locked settings (an admin can't disable these notifications) should be true
     */
    protected bool $isAlwaysActive = false;
    protected ?App $app;
    protected ?User $recipient = null;
    protected array $data = [];
    protected string $pushNotificationMessage;
    protected array $pushNotificationData = [];

    /**
     * @throws \Exception
     */
    public function sendEmail($recipient = null)
    {
        if (!$this->wantsEmailNotification(!is_null($recipient))) {
            return;
        }

        if (!$recipient) {
            $recipient = $this->recipient->email;
        }
        if (!$recipient) {
            throw new \Exception('Recipient can\'t be empty');
        }

        try {
            Mail::to($recipient)->queue($this);
        } catch (\Exception $e) {
            \Sentry::captureException($e);
        }
    }

    public function sendPushNotification()
    {
        if (!$this->wantsPushNotification()) {
            return;
        }

        $badgeCount = Notifier::getBadgeCount($this->recipient);
        Notifier::sendMessage($this->pushNotificationMessage, $this->recipient, $this->pushNotificationData, $badgeCount);
    }

    public function wantsEmailNotification(bool $isExternalRecipient = false): bool
    {
        // don't send a notification to a bot and a deleted user
        if(!$isExternalRecipient && !$this->recipientIsActiveUser()) {
            return false;
        }

        // Email notification isn't sent to tmp accounts, and when email is empty
        if($this->recipient && ($this->recipient->isTmpAccount() || $this->recipient->isMaillessAccount())) {
            return false;
        }

        if ($this->isAlwaysActive) {
            return true;
        }

        $reflection = new \ReflectionClass($this);
        $mailTemplate = $reflection->getShortName();

        if ($this->recipient) {
            $appProfile = $this->recipient->getAppProfile();
        } else {
            $appProfile = $this->app->getDefaultAppProfile();
        }

        // can't send a disabled notification
        if (!$appProfile->isActiveNotification($mailTemplate)) {
            return false;
        }

        // an external recipient doesn't have notification settings
        if($isExternalRecipient) {
            return true;
        }

        // return true if a user can't disable a notification
        if (!$appProfile->canNotificationBeDisabledByUser($mailTemplate)) {
            return true;
        }

        $userNotificationSettings = UserNotificationSetting
            ::where('user_id', $this->recipient->id)
            ->whereIn('notification', ['all', $mailTemplate])
            ->get()
            ->keyBy('notification');

        // check if a user disabled a notification
        if ($userNotificationSettings->has('all') && $userNotificationSettings['all']->mail_disabled) {
            return false;
        }
        if ($userNotificationSettings->has($mailTemplate) && $userNotificationSettings[$mailTemplate]->mail_disabled) {
            return false;
        }

        return true;
    }

    public function wantsPushNotification(): bool
    {
        // don't send a notification to a bot and a deleted user
        if(!$this->recipientIsActiveUser()) {
            return false;
        }

        $reflection = new \ReflectionClass($this);
        $mailTemplate = $reflection->getShortName();

        if(!$this->recipient->hasPushNotificationId()) {
            return false;
        }

        $appProfile = $this->recipient->getAppProfile();

        // check if a user disabled a notification
        if ($appProfile->canNotificationBeDisabledByUser($mailTemplate)) {
            $userNotificationSettings = UserNotificationSetting
                ::where('user_id', $this->recipient->id)
                ->whereIn('notification', ['all', $mailTemplate])
                ->get()
                ->keyBy('notification');

            if($userNotificationSettings->has('all') && $userNotificationSettings['all']->push_disabled) {
                return false;
            }
            if($userNotificationSettings->has($mailTemplate) && $userNotificationSettings[$mailTemplate]->push_disabled) {
                return false;
            }
        }

        if(in_array($this->recipient->app_id, [
            App::ID_WEBDEV_QUIZ,
            APP::ID_MONEYCOASTER,
        ])) {
            return true;
        }

        // All new apps want push notifications because they might use the keelearning store app
        if($this->recipient->app_id >= App::ID_ILEARN) {
            return true;
        }

        $appSettings = new AppSettings($this->recipient->app_id);
        return $appSettings->getValue('has_candy_frontend') == '1';
    }

    /**
     * Send the message using the given mailer.
     *
     * @param Factory|\Illuminate\Contracts\Mail\Mailer $mailer
     * @return void
     * @throws ReflectionException
     */
    public function send($mailer)
    {
        $this->prepareSmtpConfig();
        $mailer = app('mailer');

        return $this->withLocale($this->locale, function () use ($mailer) {
            $this->clearAppProfileCaches();
            Container::getInstance()->call([$this, 'build']);

            $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
                $this->buildFrom($message)
                    ->buildRecipients($message)
                    ->buildSubject($message)
                    ->runCallbacks($message)
                    ->buildAttachments($message);
            });
        });
    }

    private function prepareSmtpConfig()
    {
        $mailProperties = [
            'driver' => env('MAIL_MAILER', 'smtp'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'from' => env('MAIL_COURIER_ADDRESS'),
            'from_name' => env('MAIL_COURIER_NAME'),
        ];
        $dontCheckSSLCertificate = false;
        if ($this->app) {
            // we deliberately ignore $this->forceAppProfile here
            // and only use it for the replacement variables,
            // as the SMTP settings should be based on the recipient,
            // not the user triggering the mail
            if (isset($this->recipient) && $this->recipient) {
                $settings = $this->recipient->getAppProfile();
            } else {
                $settings = $this->app->getDefaultAppProfile();
            }
            $settings->clearCache();

            if ($settings->getValue('smtp_host')
                && $settings->getValue('smtp_port')
                && $settings->getValue('smtp_email')
                && $settings->getValue('smtp_username')
                && $settings->getValue('smtp_password')) {
                $mailProperties['host'] = $settings->getValue('smtp_host');
                $mailProperties['port'] = $settings->getValue('smtp_port');
                $mailProperties['from'] = $settings->getValue('smtp_email');
                $mailProperties['username'] = $settings->getValue('smtp_username');
                $mailProperties['password'] = decrypt($settings->getValue('smtp_password'));
                $mailProperties['encryption'] = $settings->getValue('smtp_encryption');
                $dontCheckSSLCertificate = true;
            } else {
                if ($customMail = $this->app->customEmailSender()) {
                    $mailProperties['from'] = $customMail;
                }
            }
            $mailProperties['from_name'] = $settings->getValue('app_name');
        }

        config([
            'mail.from.address' => $mailProperties['from'],
            'mail.from.name' => alphaNumericOnly($mailProperties['from_name']),
        ]);

        if (live() && !$this instanceof SMTPSettingsMail) {
            config([
                'mail.driver' => $mailProperties['driver'],
                'mail.host' => $mailProperties['host'],
                'mail.port' => $mailProperties['port'],
                'mail.encryption' => $mailProperties['encryption'],
                'mail.username' => $mailProperties['username'],
                'mail.password' => $mailProperties['password'],
            ]);
            if ($dontCheckSSLCertificate) {
                config([
                    'mail.stream.ssl.allow_self_signed' => true,
                    'mail.stream.ssl.verify_peer' => false,
                    'mail.stream.ssl.verify_peer_name' => false,
                ]);
            } else {
                config([
                    'mail.stream.ssl.allow_self_signed' => false,
                    'mail.stream.ssl.verify_peer' => true,
                    'mail.stream.ssl.verify_peer_name' => true,
                ]);
            }
        }
        // Reload the mail config
        (new MailServiceProvider(app()))->register();
        Mail::alwaysFrom($mailProperties['from'], $mailProperties['from_name']);
    }

    private function recipientIsActiveUser(): bool
    {
        if(!$this->recipient) {
            return false;
        }
        if ($this->recipient->is_bot || $this->recipient->deleted_at) {
            return false;
        }

        return true;
    }

    private function clearAppProfileCaches() {
        AppProfile::clearStaticCache();
        AppProfileCache::clearStaticCache();
    }
}
