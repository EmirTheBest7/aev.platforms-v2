<?php

declare(strict_types=1);

namespace Core\Services\Notifier;

interface Notifier
{
    /**
     * Delivers a plain-text notification. Must never throw and must never block
     * longer than its configured timeout; returns false on any failure.
     */
    public function send(string $text): bool;
}
