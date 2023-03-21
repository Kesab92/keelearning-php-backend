<?php

namespace App\Push;

use App\Models\App;
use App\Services\AppSettings;
use Config;
use Edujugon\PushNotification\PushNotification;
use Exception;
use Illuminate\Support\Collection;

class FCM
{
    public static function sendMessage($payload, $recipients, $appId)
    {
        if($recipients instanceof Collection) {
            $recipients = $recipients->toArray();
        }
        if(!is_array($recipients)) {
            $recipients = [$recipients];
        }
        if (! isset($payload['image'])) {
            $payload['image'] = self::getImage($appId);
        }
        /** @var \Edujugon\PushNotification\PushNotification $push */
        $push = new PushNotification('fcm');
        $message = [
            'data' => $payload,
        ];
        if(isset($payload['title'])) {
            $message['notification'] = [];
            $message['notification']['title'] = $payload['title'];
            if(isset($payload['image'])) {
                $message['notification']['image'] = $payload['image'];
            }
        }
        $push
            ->setApiKey(self::getApiKey($appId))
            ->setMessage($message)
            ->setDevicesToken($recipients)
            ->send();
    }

    private static function getApiKey($appId)
    {
        $key = Config::get('services.fcm.'.App::SLUGS[$appId].'.key');
        if($key) {
            return $key;
        }

        $appSettings = new AppSettings($appId);
        if($appSettings->getValue('has_candy_frontend')) {
            if($key) {
                return $key;
            }
            // Return the default key
            return Config::get('services.fcm.keelearning-app.key');
        }

        throw new Exception('This app can\'t send notifications yet because no FCM key is set');
    }

    private static function getImage($appId)
    {
        return Config::get('services.fcm.'.App::SLUGS[$appId].'.icon') ?: '';
    }
}
