<?php

declare(strict_types=1);

/**
 * Minimal manual autoloader for the Core\Auth\ namespace.
 *
 * This exists ONLY because ALIEV.IO V2 does not have a composer.json /
 * PSR-4 autoloader configured yet. Once Composer is introduced, delete
 * this file and register the equivalent PSR-4 mapping instead:
 *
 *   "autoload": {
 *       "psr-4": {
 *           "Core\\": "core/"
 *       }
 *   }
 *
 * Usage (see apps/account/*.php):
 *   require_once __DIR__ . '/../../core/autoload.php';
 */

spl_autoload_register(static function (string $class): void {
    $prefix = 'Core\\';

    if (!str_starts_with($class, $prefix)) {
        return; // not our namespace, let another autoloader handle it
    }

    $relativeClass = substr($class, strlen($prefix));
    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    $file = __DIR__ . DIRECTORY_SEPARATOR . $relativePath;

    if (is_file($file)) {
        require $file;
    }
});
