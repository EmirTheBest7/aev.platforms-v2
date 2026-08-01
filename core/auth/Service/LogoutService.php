<?php

declare(strict_types=1);

namespace Core\Auth\Service;

use Core\Auth\Contracts\AuditLoggerInterface;
use Core\Auth\Contracts\SessionHandlerInterface;

/**
 * Orchestrates the logout flow: capture identity -> destroy session -> audit.
 */
final class LogoutService
{
    public function __construct(
        private readonly SessionHandlerInterface $session,
        private readonly AuditLoggerInterface $auditLogger,
    ) {
    }

    public function logout(string $ipAddress, ?string $userAgent = null): void
    {
        $userId = $this->session->currentUserId();

        $this->session->destroy();

        // Logged after destroy() — the captured $userId is a local copy,
        // so we can still attribute the event even though the session
        // itself no longer exists.
        $this->auditLogger->log('logout', $userId, $ipAddress, $userAgent);
    }
}
