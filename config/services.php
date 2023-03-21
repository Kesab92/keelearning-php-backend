<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'fcm' => [
        'keelearning-app' => [
            'key'  => env('FCM_KEY_KEELEARNING_APP'),
        ],
        'lingo' => [
            'key'  => env('FCM_KEY_LINGO'),
            'icon' => 'www/static/img/notification.png',
        ],
        'genoapp' => [
            'key'  => env('FCM_KEY_GENOAPP'),
            'icon' => 'www/static/img/logo.png',
        ],
        'webdev' => [
            'key'  => env('FCM_KEY_WEBDEV'),
            'icon' => 'www/img/logo-512.png',
        ],
        'opengrid' => [
            'key'  => env('FCM_KEY_OPENGRID'),
            'icon' => 'www/static/img/logo.png',
        ],
        'raiffeisen' => [
            'key'  => env('FCM_KEY_RAIFFEISEN'),
            'icon' => 'www/static/favicons/icon-512x512.png',
        ],
        'bayer' => [
            'key'  => env('FCM_KEY_BAYER'),
            'icon' => 'www/static/favicons/icon-512x512.png',
        ],
        'demo' => [
            'key'  => env('FCM_KEY_DEMO'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'talent_thinking' => [
            'key'  => env('FCM_KEY_TALENTTHINKING'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'decisio' => [
            'key'  => env('FCM_KEY_DECISIO'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'hno' => [
            'key'  => env('FCM_KEY_HNO'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'signia-pro' => [
            'key'  => env('FCM_KEY_SIGNIAPRO'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'stayathome' => [
            'key'  => env('FCM_KEY_STAYATHOME'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'moneycoaster' => [
            'key'  => env('FCM_KEY_MONEYCOASTER'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'orl' => [
            'key'  => env('FCM_KEY_ORL'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'wmf' => [
            'key'  => env('FCM_KEY_WMF'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'vftc' => [
            'key'  => env('FCM_KEY_VFTC'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'fit-for-trade' => [
            'key'  => env('FCM_KEY_FITFORTRADE'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
        'blume2000' => [
            'key'  => env('FCM_KEY_BLUME2000'),
            'icon' => 'www/static/images/icons/icon-512x512.png',
        ],
    ],

    'raven' => [
        'dsn'   => env('RAVEN_DSN'),
        'level' => env('LOG_LEVEL', 'error'),
    ],

    'deepstream' => [
        'disabled' => env('DEEPSTREAM_DISABLED', false),
        'url' => env('DEEPSTREAM_URL'),
        'token' => env('DEEPSTREAM_TOKEN'),
    ],

    'slack' => [
        'horizon' => env('SLACK_HORIZON', null),
    ],

    'azure' => [
        'tenant' =>  env('AZURE_MEDIA_TENANT', null),
        'clientId' => env('AZURE_MEDIA_CLIENT_ID', null),
        'clientKey' => env('AZURE_MEDIA_CLIENT_KEY', null),
        'restApiEndpoint' => env('AZURE_MEDIA_REST_API_ENDPOINT', null),
    ],

    'samba' => [
        'token' => env('SAMBA_TOKEN'),
        'endpoint' => 'https://mywebinar.app/api/2/',
    ],

];
