<?php

declare(strict_types=1);

use Core\Helpers\Env;

return [
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_ENV', 'production') !== 'production' && Env::bool('APP_DEBUG', false),
    'url' => rtrim(Env::get('APP_URL', 'http://localhost:8080') ?? '', '/'),
    'key' => Env::get('APP_KEY', ''),
    'timezone' => Env::get('APP_TIMEZONE', 'UTC'),
    'log_channel' => Env::get('LOG_CHANNEL', 'file'),
    'log_level' => Env::get('LOG_LEVEL', 'info'),
];
