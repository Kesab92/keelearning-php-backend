<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Spec Source
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default spec source that should be used
    | by the framework.
    |
    */

    'default' => env('SPEC_SOURCE', 'remote'),

    /*
    |--------------------------------------------------------------------------
    | Sources
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many sources as you wish, and you
    | may even configure multiple source of the same type. Defaults have
    | been setup for each driver as an example of the required options.
    |
    */

    'sources' => [
        'local' => [
            'source' => 'local',
            'base_path' => env('SPEC_PATH'),
        ],

        'remote' => [
            'source' => 'remote',
            'base_path' => env('SPEC_PATH', 'https://api.swaggerhub.com/apis/keeunit/keelearning-public-api/1.1.0'),
            'params' => env('SPEC_URL_PARAMS', '?resolved=true'),
        ],

        'github' => [
            'source' => 'github',
            'base_path' => env('SPEC_GITHUB_PATH'),
            'repo' => env('SPEC_GITHUB_REPO'),
            'token' => env('SPEC_GITHUB_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Configure path defaults, like prefixes.
    |
    */

    'path_prefix' => '/api/public/v1',
];
