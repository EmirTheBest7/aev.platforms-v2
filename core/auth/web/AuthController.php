<?php

declare(strict_types=1);

namespace Core\Auth\Web;

use Core\Auth\AuthFacade;
use Core\Auth\DTO\LoginData;
use Core\Auth\DTO\RegistrationData;
use Core\Auth\Exception\AccountLockedException;
use Core\Auth\Exception\DuplicateAccountException;
use Core\Auth\Exception\InvalidCredentialsException;
use Core\Auth\Exception\ValidationException;
use Core\Helpers\View;
use Core\Logging\Logger;
use Core\Routing\HttpException;
use Core\Routing\Request;
use Core\Routing\Response;
use Core\Security\FormGuard;

/**
 * Log in, sign up, log out and the (honest) password-reset page on top of core/auth.
 * The session, CSRF token and password hashing are the shared ones from Application / core/auth.
 *
 * Messages never say which half of a credential pair was wrong, and "email or nickname taken" is one message.
 */
final class AuthController
{
    private const FLASH_KEY = '_auth_flash';
    private const LOGIN_FAILED = 'Email or password is incorrect, or the account is temporarily locked.';

    public function __construct(
        private readonly bool $enabled,
        private readonly View $view,
        private readonly AuthFacade $auth,
        private readonly FormGuard $guard,
        private readonly Logger $logger,
        private readonly string $contactEmail,
    ) {}

    public function show(Request $request): Response
    {
        $this->assertEnabled();
        $tokens = $this->guard->tokens();

        if ($this->auth->session()->isAuthenticated()) {
            return Response::redirect('/');
        }

        $flash = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        $flash = is_array($flash) ? $flash : [];

        $referral = $request->query['refer'] ?? '';
        $referral = is_string($referral) && preg_match('/^[A-Za-z0-9_-]{1,40}$/D', $referral) === 1 ? $referral : '';

        $html = $this->view->render('pages/auth/index', [
            'csrf' => $tokens['csrf'],
            'ts' => $tokens['ts'],
            'showRegister' => ($flash['face'] ?? '') === 'register' || ($referral !== '' && $flash === []),
            'loginErrors' => $this->strings($flash['login'] ?? []),
            'registerErrors' => $this->strings($flash['register'] ?? []),
            'notice' => is_string($flash['notice'] ?? null) ? $flash['notice'] : '',
            'old' => $this->strings($flash['old'] ?? []),
            'referral' => $referral,
            'navbar' => ['backHref' => '/', 'backIcon' => 'uil-estate'],
        ], $this->meta('ΛΞV | Log In', '/home/auth'), 'page');

        return (new Response($html))->withHeader('Cache-Control', 'no-store');
    }

    public function login(Request $request): Response
    {
        $this->assertEnabled();
        $this->guard->requireCsrf($request, 'auth-login');
        $ip = $this->guard->clientAddress($request);
        $this->guard->requireWithinLimit('auth-login', $ip, $this->guard->pseudonym($ip), 10, 60);

        $email = strtolower(trim($request->input('logemail')));

        try {
            $this->auth->login(new LoginData($email, $request->input('logpass'), $ip, $this->userAgent($request)));
        } catch (ValidationException | InvalidCredentialsException | AccountLockedException) {
            return $this->back(['login' => [self::LOGIN_FAILED], 'old' => ['logemail' => mb_substr($email, 0, 254)], 'face' => 'login']);
        } catch (\PDOException $e) {
            $this->logger->error('auth.database', ['exception' => $e]);
            throw new HttpException(503);
        }

        $this->guard->rotate();

        return Response::redirect('/');
    }

    public function register(Request $request): Response
    {
        $this->assertEnabled();
        $guard = $this->guard->check($request, 'auth-register', 5, 10);

        if ($guard['outcome'] === FormGuard::HONEYPOT) {
            return $this->back(['notice' => 'Account created. You can log in now.', 'face' => 'login']);
        }
        if ($guard['outcome'] === FormGuard::TOO_FAST) {
            return $this->back(['register' => ['Please wait a moment and submit the form again.'], 'face' => 'register']);
        }

        $old = [
            'regname' => mb_substr(trim($request->input('regname')), 0, 80),
            'regnick' => mb_substr(trim($request->input('regnick')), 0, 32),
            'regemail' => mb_substr(strtolower(trim($request->input('regemail'))), 0, 254),
        ];

        $referral = trim($request->input('regref'));
        $data = new RegistrationData(
            email: $old['regemail'],
            username: $old['regnick'],
            password: $request->input('regpass'),
            passwordConfirmation: $request->input('regpass2'),
            displayName: $old['regname'] === '' ? null : $old['regname'],
            referralCode: $referral === '' ? null : $referral,
        );

        try {
            $this->auth->register($data, $this->guard->clientAddress($request), $this->userAgent($request));
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $fieldErrors) {
                foreach ((array) $fieldErrors as $message) {
                    $messages[] = (string) $message;
                }
            }

            return $this->back(['register' => $messages, 'old' => $old, 'face' => 'register']);
        } catch (DuplicateAccountException) {
            return $this->back(['register' => ['That email or nickname is not available.'], 'old' => $old, 'face' => 'register']);
        } catch (\PDOException $e) {
            $this->logger->error('auth.database', ['exception' => $e]);
            throw new HttpException(503);
        }

        $this->guard->rotate();

        return $this->back(['notice' => 'Account created. You can log in now.', 'face' => 'login']);
    }

    public function logout(Request $request): Response
    {
        $this->assertEnabled();
        $this->guard->requireCsrf($request, 'auth-logout');

        $this->auth->logout($this->guard->clientAddress($request), $this->userAgent($request));

        return Response::redirect('/');
    }

    public function reset(Request $request): Response
    {
        $this->assertEnabled();

        $html = $this->view->render('pages/auth/reset', ['email' => $this->contactEmail, 'navbar' => null], $this->meta('ΛΞV | Password reset', '/home/auth/reset'), 'page');

        return (new Response($html))->withHeader('Cache-Control', 'no-store');
    }

    /** The account pages do not exist while AUTH_ENABLED is off. */
    private function assertEnabled(): void
    {
        if (!$this->enabled) {
            throw new HttpException(404);
        }
    }

    /** @param array<string, mixed> $flash */
    private function back(array $flash): Response
    {
        $_SESSION[self::FLASH_KEY] = $flash;

        return Response::redirect('/home/auth');
    }

    /** @return array<string, mixed> */
    private function meta(string $title, string $path): array
    {
        return [
            'title' => $title,
            'description' => 'Log in to ΛΞV or create an account.',
            'path' => $path,
            'noindex' => true,
            'bodyClass' => 'page-auth',
            'styles' => ['/assets/css/auth-fonts.css', '/assets/vendor/bootstrap/bootstrap.min.css', '/assets/css/auth.css'],
            'scripts' => ['/assets/js/auth.js'],
        ];
    }

    private function userAgent(Request $request): ?string
    {
        $agent = $request->header('User-Agent');

        return $agent === null ? null : mb_substr($agent, 0, 255);
    }

    /**
     * @return list<string>|array<string, string>
     */
    private function strings(mixed $value): array
    {
        return is_array($value) ? array_filter($value, is_string(...)) : [];
    }
}
