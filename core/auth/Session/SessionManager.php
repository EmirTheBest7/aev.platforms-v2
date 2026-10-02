<?php

declare(strict_types=1);

namespace Core\Auth\Session;

use Core\Auth\Config\AuthConfig;
use Core\Auth\Contracts\SessionHandlerInterface;
use Core\Auth\DTO\AuthenticatedUser;

/**
 * Secure native PHP session management.
 *
 * This is the ONLY class in core/auth allowed to touch $_SESSION or the
 * session_* functions directly. Everything else goes through
 * SessionHandlerInterface.
 */
final class SessionManager implements SessionHandlerInterface
{
    private const string USER_ID_KEY = '_auth_user_id';
    private const string AUTHENTICATED_AT_KEY = '_auth_authenticated_at';

    public function __construct(private readonly AuthConfig $config)
    {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Never adopt a session id the server did not issue (session fixation), and keep ids out of URLs.
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name($this->config->sessionName);

        session_set_cookie_params([
            'lifetime' => $this->config->sessionLifetimeSeconds,
            'path' => '/',
            'domain' => '',
            'secure' => $this->config->sessionCookieSecure,
            'httponly' => $this->config->sessionCookieHttpOnly,
            'samesite' => $this->config->sessionCookieSameSite,
        ]);

        session_start();
    }

    public function login(AuthenticatedUser $user): void
    {
        $this->start();

        // Prevent session fixation: issue a brand new session id on
        // privilege change, keep existing session data intact.
        session_regenerate_id(true);

        $_SESSION[self::USER_ID_KEY] = $user->id;
        $_SESSION[self::AUTHENTICATED_AT_KEY] = time();
    }

    public function destroy(): void
    {
        $this->start();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ],
            );
        }

        session_destroy();
    }

    public function currentUserId(): ?int
    {
        $this->start();

        if (!isset($_SESSION[self::USER_ID_KEY])) {
            return null;
        }

        // The cookie lifetime is only a hint to the browser: enforce the lifetime on the server as well.
        $since = (int) ($_SESSION[self::AUTHENTICATED_AT_KEY] ?? 0);
        if ($since === 0 || time() - $since > $this->config->sessionLifetimeSeconds) {
            unset($_SESSION[self::USER_ID_KEY], $_SESSION[self::AUTHENTICATED_AT_KEY]);

            return null;
        }

        return (int) $_SESSION[self::USER_ID_KEY];
    }

    public function isAuthenticated(): bool
    {
        return $this->currentUserId() !== null;
    }
}
