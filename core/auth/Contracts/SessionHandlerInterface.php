<?php

declare(strict_types=1);

namespace Core\Auth\Contracts;

use Core\Auth\DTO\AuthenticatedUser;

/**
 * Contract for session lifecycle management.
 *
 * Only the concrete implementation is allowed to touch $_SESSION directly;
 * every other part of the module interacts with sessions through this
 * interface.
 */
interface SessionHandlerInterface
{
    public function start(): void;

    /**
     * Regenerates the session id (fixation prevention) and stores the
     * authenticated user's identity in the session.
     */
    public function login(AuthenticatedUser $user): void;

    /**
     * Destroys the session completely (data + cookie + session id).
     */
    public function destroy(): void;

    public function currentUserId(): ?int;

    public function isAuthenticated(): bool;
}
