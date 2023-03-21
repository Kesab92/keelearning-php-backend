<?php

namespace App\Push;

use App\Models\App;
use Edujugon\PushNotification\PushNotification;

class APN
{
    public static function sendMessage($title, $data, $recipient, $appId)
    {
        /** @var \Edujugon\PushNotification\PushNotification $push */
        $push = new PushNotification('apn');
        $message = [
            'aps' => [
                'alert' => $title,
            ],
        ];
        if (isset($data['badge'])) {
            $message['aps']['badge'] = $data['badge'];
        }
        if (isset($data['content-available'])) {
            $message['aps']['content-available'] = $data['content-available'];
        }
        if (isset($data['custom'])) {
            $message['extraPayload']['custom'] = $data['custom'];
        }
        $push
            ->setConfig([
                'certificate' => self::getCertificate($appId),
            ])
            ->setMessage($message)
            ->setDevicesToken([$recipient])
            ->send();
    }

    private static function getCertificate($appId)
    {
        switch ($appId) {
            case App::ID_WEBDEV_QUIZ:
                return storage_path('app/production_de.sopamo.keeunit.quizapp.webdev.pem');
                break;
            case App::ID_LINGOMINT:
                return storage_path('app/de.sopamo.keeunit.keelearning.lingomint.pem');
                break;
            case App::ID_GENOAKADEMIE:
                return storage_path('app/de.sopamo.keeunit.keelearning.genoapp.pem');
                break;
            case App::ID_OPENGRID:
                return storage_path('app/de.sopamo.keeunit.keelearning.opengrid.pem');
                break;
            case App::ID_RAIFFEISEN:
                return storage_path('app/de.sopamo.keeunit.keelearning.raiffeisen.pem');
                break;
            case App::ID_KEEUNIT_DEMO:
                return storage_path('app/de.sopamo.keeunit.keelearning.demolernapp.pem');
                break;
            case App::ID_TALENT_THINKING:
                return storage_path('app/de.sopamo.keeunit.keelearning.talentthinking.pem');
                break;
            case App::ID_VOLKSBANKEN_RAIFFEISENBANKEN:
                return storage_path('app/de.sopamo.keeunit.keelearning.vrfinanzfuchs.pem');
                break;
            case App::ID_DECISIO:
                return storage_path('app/de.sopamo.keeunit.keelearning.decisio.pem');
                break;
            case App::ID_HNO:
                return storage_path('app/de.sopamo.keeunit.keelearning.hno.pem');
                break;
            case App::ID_SIGNIA_PRO:
                return storage_path('app/de.sopamo.keeunit.keelearning.signiapro.pem');
                break;
            case App::ID_STAYATHOME:
                return storage_path('app/de.sopamo.keeunit.keelearning.stayathome.pem');
                break;
            case App::ID_MONEYCOASTER:
                return storage_path('app/de.sopamo.keeunit.keelearning.moneycoaster.pem');
                break;
            default:
                throw new \Exception('This app can\'t send notifications on iOS yet because no certificate is set');
        }
    }
}
