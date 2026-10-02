<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'driver' => Env::get('NOTIFY_DRIVER', 'log'),
    'timeout' => Env::int('NOTIFY_TIMEOUT_SECONDS', 4),
    'telegram' => [
        'token' => Env::get('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => Env::get('TELEGRAM_CHAT_ID', ''),
    ],
];
