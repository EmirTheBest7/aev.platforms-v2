<?php

declare(strict_types=1);

namespace Core\Auth\Security;

use Core\Auth\Config\AuthConfig;
use Core\Auth\Contracts\TokenGeneratorInterface;

/**
 * CSRF token generation and validation, backed by the PHP session.
 *
 * This class provides CSRF *support* for the platform — it does not wire
 * itself into any HTTP request handling. Callers (e.g. api/v2/auth/*.php)
 * are responsible for calling validate() against the submitted token on
 * state-changing requests.
 *
 * Requires an active session (SessionHandler::start() must have run).
 */
final class CsrfProtection
{
    public function __construct(
        private readonly AuthConfig $config,
        private readonly TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    /**
     * Returns the current CSRF token, generating one if none exists yet.
     */
    public function getToken(): string
    {
        if (!isset($_SESSION[$this->config->csrfSessionKey])) {
            $_SESSION[$this->config->csrfSessionKey] = $this->tokenGenerator->generate(
                $this->config->csrfTokenLength,
            );
        }

        return $_SESSION[$this->config->csrfSessionKey];
    }

    /**
     * Forces generation of a fresh token, invalidating the previous one.
     * Useful after a successful state-changing request (token rotation).
     */
    public function rotateToken(): string
    {
        $_SESSION[$this->config->csrfSessionKey] = $this->tokenGenerator->generate(
            $this->config->csrfTokenLength,
        );

        return $_SESSION[$this->config->csrfSessionKey];
    }

    /**
     * Validates a submitted token against the session token using a
     * timing-safe comparison.
     */
    public function validate(?string $submittedToken): bool
    {
        $sessionToken = $_SESSION[$this->config->csrfSessionKey] ?? null;

        if (!is_string($sessionToken) || !is_string($submittedToken) || $submittedToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $submittedToken);
    }
}
