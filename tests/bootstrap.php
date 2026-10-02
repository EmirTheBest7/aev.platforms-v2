<?php

declare(strict_types=1);

// Tests assert on published assets (public/build, public/home/_api): make sure they exist and are current.
require dirname(__DIR__) . '/vendor/autoload.php';
passthru(PHP_BINARY . ' ' . escapeshellarg(dirname(__DIR__) . '/scripts/build.php') . ' > /dev/null');
