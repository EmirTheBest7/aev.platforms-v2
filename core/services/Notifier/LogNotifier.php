<?php

declare(strict_types=1);

namespace Core\Services\Notifier;

use Core\Logging\Logger;

/** Development/default driver: records that a notification would have been sent (no content). */
final class LogNotifier implements Notifier
{
    public function __construct(private readonly Logger $logger) {}

    public function send(string $text): bool
    {
        $this->logger->info('notify.logged', ['length' => mb_strlen($text)]);

        return true;
    }
}
