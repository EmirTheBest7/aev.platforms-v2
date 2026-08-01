<?php

declare(strict_types=1);

namespace Core\Auth\Audit;

use Core\Auth\Contracts\AuditLoggerInterface;
use JsonException;
use PDO;

/**
 * Concrete audit logger, persisting security-relevant events for human
 * review (admin tooling, incident response, compliance).
 *
 * Distinct from LoginAttemptRepository: that class exists to feed
 * real-time brute-force decisions; this class exists to answer
 * "what happened, and when" after the fact.
 *
 * Suggested event types (not enforced, kept as free-form strings so new
 * event types don't require a schema change):
 *   - registration.success
 *   - registration.failed
 *   - login.success
 *   - login.failed
 *   - login.locked
 *   - logout
 *   - password.rehashed
 *
 * Expected schema: see database/reference/auth_schema.sql
 */
final class AuditLogger implements AuditLoggerInterface
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function log(
        string $eventType,
        ?int $userId,
        string $ipAddress,
        ?string $userAgent,
        array $metadata = [],
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO auth_audit_log (user_id, event_type, ip_address, user_agent, metadata, created_at)
             VALUES (:user_id, :event_type, :ip, :agent, :metadata, NOW())',
        );

        try {
            $encodedMetadata = json_encode($metadata, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            // Never let a logging failure break the calling flow — audit
            // logging must be best-effort, not a hard dependency of auth.
            $encodedMetadata = '{}';
        }

        $stmt->execute([
            'user_id' => $userId,
            'event_type' => $eventType,
            'ip' => $ipAddress,
            'agent' => $userAgent,
            'metadata' => $encodedMetadata,
        ]);
    }
}
