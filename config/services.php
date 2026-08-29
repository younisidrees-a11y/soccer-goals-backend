<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        // Every AI writer on the site (match reports, club news, transfers,
        // previews, club history, live commentary) shares this one model.
        // Haiku was cheap but visibly weaker at actually holding onto the
        // "sound human, don't invent details" style rules baked into every
        // prompt - it's what let a match report state the wrong day of the
        // week despite being told not to. Defaulting to Sonnet here too so
        // a fresh environment without an explicit override doesn't quietly
        // fall back to the weaker tier.
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
    ],

    'football_data' => [
        'key' => env('FOOTBALL_DATA_API_KEY'),
    ],

    'api_football' => [
        'key' => env('API_FOOTBALL_KEY'),
    ],

];
