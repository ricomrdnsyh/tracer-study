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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sso' => [
        'api_url'        => env('SSO_API_URL'),
        'public_url'     => env('SSO_PUBLIC_URL'),
        'authorize_url'  => env('SSO_AUTHORIZE_URL'),
        'data_url'       => env('SSO_DATA_URL'),
        'me_url'         => env('SSO_ME_URL'),
        'x_token'        => env('SSO_X_TOKEN'),
        'dev_id'         => env('SSO_DEV_ID'),
        'force_ipv4'     => filter_var(env('SSO_FORCE_IPV4', true), FILTER_VALIDATE_BOOLEAN),
        'force_http_1_1' => filter_var(env('SSO_FORCE_HTTP_1_1', true), FILTER_VALIDATE_BOOLEAN),
    ],

];
