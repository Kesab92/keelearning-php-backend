<?php

namespace App\Jobs;

use App\Models\FcmToken;
use App\Models\User;
use App\Push\APN;
use App\Push\FCM;
use App\Services\AppProfileSettings;
use App\Services\AppSettings;
use App\Services\QueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMobileNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $text;
    /**
     * @var User
     */
    private $recipient;
    private $data;
    private $badgeCount;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 2;

    /**
     * Create a new job instance.
     *
     * @param      $text
     * @param User $recipient
     * @param      $data
     * @param      $badgeCount
     */
    public function __construct($text, User $recipient, $data, $badgeCount)
    {
        $this->queue = QueuePriority::HIGH;
        $this->text = $text;
        $this->recipient = $recipient;
        $this->data = $data;
        $this->badgeCount = $badgeCount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $messageData = [
            'custom' => $this->data,
        ];
        if ($this->badgeCount !== false) {
            $messageData['badge'] = $this->badgeCount;
            $messageData['content-available'] = 1;
        }
        $data = $this->data;
        $appSettings = new AppSettings($this->recipient->app_id);
        if($appSettings->getValue('has_candy_frontend')) {
            $this->sendCandyNotification();
        } else {
            if (strlen($this->recipient->fcm_id) > 0) {
                if ($this->text) {
                    $data['title'] = $this->text;
                } else {
                    unset($data['title']);
                }
                FCM::sendMessage($data, $this->recipient->fcm_id, $this->recipient->app_id);
            }
            if (strlen($this->recipient->gcm_id_browser) > 0) {
                if ($this->text) {
                    $data['title'] = $this->text;
                }
                FCM::sendMessage($data, $this->recipient->gcm_id_browser, $this->recipient->app_id);
            }
            if (strlen($this->recipient->apns_id) > 0) {
                APN::sendMessage($this->text, $messageData, $this->recipient->apns_id, $this->recipient->app_id);
            }
        }
    }

    private function sendCandyNotification()
    {
        $tokens = FcmToken::where('user_id', $this->recipient->id)->get();
        if(!$tokens) {
            return;
        }
        $data = $this->data;
        if ($this->text) {
            $data['title'] = $this->text;
        } else {
            unset($data['title']);
        }
        FCM::sendMessage($data, $tokens->pluck('token')->toArray(), $this->recipient->app_id);
    }

    public function tags()
    {
        return ['internal-appid:'.$this->recipient->app_id];
    }
}
