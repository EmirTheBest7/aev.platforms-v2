<?php

declare(strict_types=1);

use App\Support\Env;

/**
 * Third-party integrations and public destinations. Every value comes from the environment.
 * A destination left empty is NOT invented: the UI keeps its control and shows the designed
 * "pending" notification (see docs/MAIN-PAGE.md §5, status OWNER DECISION REQUIRED).
 */
return [
    'auth_enabled' => Env::bool('AUTH_ENABLED', false),

    // Public (pk.) token of the Contact page map; restrict it by URL in the Mapbox dashboard. Empty = no map.
    'mapbox_token' => Env::get('MAPBOX_TOKEN', ''),

    'intergram' => [
        'chat_id' => Env::get('INTERGRAM_CHAT_ID', ''),
        'server' => 'https://www.intergram.xyz',
        'title_open' => 'Contact Support 📺',
        'intro' => "ΛΞV: Hello! How can we help you?\n\nLang: 🇬🇧🇨🇿🇺🇦🇷🇺",
        'main_color' => '#000',
    ],

    'destinations' => [
        // Owner-declared docs home is https://docs.aliev.io (README), but aliev.io is currently unregistered;
        // set DOCS_URL once it resolves. Empty = pending notification.
        'docs' => Env::get('DOCS_URL', ''),
        'instagram' => Env::get('SOCIAL_INSTAGRAM', 'https://www.instagram.com/aev.platforms/'),
        'telegram' => Env::get('SOCIAL_TELEGRAM', 'https://t.me/aev_platforms'),
        'telegram_news' => Env::get('SOCIAL_TELEGRAM_NEWS', 'https://t.me/s/aev_platforms'),
        'youtube' => Env::get('SOCIAL_YOUTUBE', ''),
        'facebook' => Env::get('SOCIAL_FACEBOOK', ''),
        'twitter' => Env::get('SOCIAL_TWITTER', ''),
        'qirimtalk' => Env::get('QIRIMTALK_URL', ''),
        'email' => 'hello@aliev.io',
    ],
];
