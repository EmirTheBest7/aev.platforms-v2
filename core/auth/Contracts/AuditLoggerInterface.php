<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

/**
 * Contract for the human-facing security audit trail.
 *
 * Distinct from LoginAttemptRepositoryInterface: this log exists for
 * accountability, incident review, and future admin tooling — not for
 * real-time security decisions.
 */
interface AuditLoggerInterface
{
    /**
     * @param array<string, mixed> $metadata arbitrary structured context (json-encoded internally)
     */
    public function log(
        string $eventType,
        ?int $userId,
        string $ipAddress,
        ?string $userAgent,
        array $metadata = [],
    ): void;
}
