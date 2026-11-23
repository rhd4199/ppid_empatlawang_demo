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

    // Main admin seeded by UserSeeder (env works with config:cache only through config)
    'admin' => [
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    // Static bearer token for /api/* (see API.md)
    'api' => [
        'token' => env('API_TOKEN'),
        'inject_token' => env('API_INJECT_TOKEN'), // limited: news & galleries create/update only
    ],

    // Larika WhatsApp API (https://gateway.larika.id/docs.md). Key from env only: never stored in the DB or sent to the browser.
    'larika' => [
        'key' => env('LARIKA_KEY'),
        'url' => env('LARIKA_URL', 'https://gateway.larika.id'),
        // secret returned by POST /v1/webhooks; Larika sends it as x-larika-webhook-key
        'webhook_key' => env('LARIKA_WEBHOOK_KEY'),
    ],

    // Fixed keyword answers (AssistantFallback) when no AI is connected. Off by default (owner, 2026-11-23): templates
    // answered questions they did not understand; without AI the bot stays silent and officers answer.
    'assistant' => [
        'keyword_fallback' => (bool) env('ASSISTANT_KEYWORD_FALLBACK', false),
    ],

    // Portal Data Empat Lawang (public API /api/v1, docs at <url>/dokumentasi-api/). Change DATA_PORTAL_URL when the portal moves
    // (dev preview now, production domain later); empty = the portal_* AI tools are off.
    'data_portal' => [
        'url' => env('DATA_PORTAL_URL', 'https://dev-keruani-empatlawang-portal-155c99caa1.larika.id'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        // gpt-4.1-mini follows "use the tools, don't answer from memory"; gpt-4o-mini invented local facts (2026-11-23).
        'model' => env('OPENAI_MODEL', 'gpt-4.1-mini'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        // Model used with the Responses API web_search tool (cari_web); empty = no web search.
        'search_model' => env('OPENAI_SEARCH_MODEL', 'gpt-4o-mini'),
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

];
