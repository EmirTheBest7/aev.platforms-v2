<?php

declare(strict_types=1);

namespace Core\Auth;

use Core\Auth\Audit\AuditLogger;
use Core\Auth\Config\AuthConfig;
use Core\Auth\Contracts\AuditLoggerInterface;
use Core\Auth\Contracts\LoginAttemptRepositoryInterface;
use Core\Auth\Contracts\SessionHandlerInterface;
use Core\Auth\Contracts\TokenGeneratorInterface;
use Core\Auth\Contracts\UserRepositoryInterface;
use Core\Auth\DTO\AuthenticatedUser;
use Core\Auth\DTO\LoginData;
use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\AccountLockedException;
use Core\Auth\Exception\DuplicateAccountException;
use Core\Auth\Exception\InvalidCredentialsException;
use Core\Auth\Exception\ValidationException;
use Core\Auth\Repository\LoginAttemptRepository;
use Core\Auth\Repository\UserRepository;
use Core\Auth\Security\BruteForceGuard;
use Core\Auth\Security\CsrfProtection;
use Core\Auth\Security\PasswordHasher;
use Core\Auth\Security\TokenGenerator;
use Core\Auth\Service\LoginService;
use Core\Auth\Service\LogoutService;
use Core\Auth\Service\RegistrationService;
use Core\Auth\Session\SessionManager;
use Core\Auth\Validation\LoginValidator;
use Core\Auth\Validation\RegistrationValidator;
use PDO;

/**
 * Single entry point into the authentication module.
 *
 * Wires up every dependency (repositories, security helpers, services)
 * behind a small, clean public API. Callers — most notably the future
 * api/v2/auth/*.php endpoints — should depend on this facade rather than
 * constructing individual services by hand.
 *
 * Example (from api/v2/auth/login.php, illustrative only — not part of
 * this module):
 *
 *   $auth = AuthFacade::create($pdo);
 *   $auth->csrf()->validate($_POST['csrf_token'] ?? null);
 *   $user = $auth->login(new LoginData($email, $password, $ip, $userAgent));
 */
final class AuthFacade
{
    private readonly RegistrationService $registrationService;
    private readonly LoginService $loginService;
    private readonly LogoutService $logoutService;
    private readonly CsrfProtection $csrfProtection;
    private readonly SessionHandlerInterface $session;

    public function __construct(
        private readonly AuthConfig $config,
        UserRepositoryInterface $users,
        LoginAttemptRepositoryInterface $loginAttempts,
        AuditLoggerInterface $auditLogger,
        SessionHandlerInterface $session,
        TokenGeneratorInterface $tokenGenerator,
    ) {
        $hasher = new PasswordHasher($config);
        $bruteForceGuard = new BruteForceGuard($config, $loginAttempts, $users);

        $this->session = $session;
        $this->csrfProtection = new CsrfProtection($config, $tokenGenerator);

        $this->registrationService = new RegistrationService(
            new RegistrationValidator($config),
            $users,
            $hasher,
            $auditLogger,
        );

        $this->loginService = new LoginService(
            new LoginValidator(),
            $users,
            $hasher,
            $bruteForceGuard,
            $session,
            $auditLogger,
        );

        $this->logoutService = new LogoutService($session, $auditLogger);
    }

    /**
     * Convenience factory wiring up default repository/session
     * implementations from a shared PDO connection.
     *
     * Works with both MySQL/MariaDB and PostgreSQL connections — the
     * underlying repositories detect the driver automatically via
     * DatabaseDriver::fromPdo($pdo). Build the PDO with
     * PdoConnection::forMysql(...)->connect() or
     * PdoConnection::forPostgres(...)->connect().
     */
    public static function create(PDO $pdo, ?AuthConfig $config = null): self
    {
        $config ??= new AuthConfig();

        return new self(
            config: $config,
            users: new UserRepository($pdo),
            loginAttempts: new LoginAttemptRepository($pdo),
            auditLogger: new AuditLogger($pdo),
            session: new SessionManager($config),
            tokenGenerator: new TokenGenerator(),
        );
    }

    /**
     * @throws ValidationException
     * @throws DuplicateAccountException
     */
    public function register(RegistrationData $data, string $ipAddress, ?string $userAgent = null): AuthenticatedUser
    {
        return $this->registrationService->register($data, $ipAddress, $userAgent);
    }

    /**
     * @throws ValidationException
     * @throws AccountLockedException
     * @throws InvalidCredentialsException
     */
    public function login(LoginData $data): AuthenticatedUser
    {
        return $this->loginService->login($data);
    }

    public function logout(string $ipAddress, ?string $userAgent = null): void
    {
        $this->logoutService->logout($ipAddress, $userAgent);
    }

    public function csrf(): CsrfProtection
    {
        return $this->csrfProtection;
    }

    public function session(): SessionHandlerInterface
    {
        return $this->session;
    }

    public function config(): AuthConfig
    {
        return $this->config;
    }
}
