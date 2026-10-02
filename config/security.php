<?php

declare(strict_types=1);

use App\Support\Env;

$proxies = array_values(array_filter(array_map('trim', explode(',', Env::get('TRUSTED_PROXIES', '') ?? ''))));

return [
    'trusted_proxies' => $proxies,
    'force_https' => Env::get('APP_ENV', 'production') === 'production' && Env::bool('FORCE_HTTPS', true),
];
