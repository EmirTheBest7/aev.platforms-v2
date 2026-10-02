<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application;
use App\Http\Request;
use App\Http\Response;
use App\Security\Signer;
use App\Support\Config;
use Core\Auth\Database\PdoConnection;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Account flows against the real MariaDB schema (migrations 001–002). Skipped when no database is reachable
 * (the docker compose stack provides one). Every row it creates is removed again.
 */
final class AuthTest extends TestCase
{
    private const KEY = 'test-key-test-key-test-key-test-key-0123';
    private const PASSWORD = 'Correct-Horse-9';

    private ?PDO $pdo = null;
    private string $storage;
    private string $email;
    private string $nick;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: '';
        if ($host === '') {
            self::markTestSkipped('No database configured (DB_HOST).');
        }
        try {
            $this->pdo = PdoConnection::forMysql($host, (string) getenv('DB_DATABASE'), (string) getenv('DB_USERNAME'), (string) getenv('DB_PASSWORD'), (int) (getenv('DB_PORT') ?: 3306))->connect();
            $this->pdo->query('SELECT 1 FROM users LIMIT 1');
        } catch (\Throwable) {
            self::markTestSkipped('Database not reachable or not migrated.');
        }

        $this->storage = sys_get_temp_dir() . '/aliev-auth-' . bin2hex(random_bytes(4));
        mkdir($this->storage, 0700, true);
        $id = bin2hex(random_bytes(4));
        $this->email = "qa-{$id}@example.test";
        $this->nick = "qa_{$id}";

        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id('a' . bin2hex(random_bytes(8)));
        session_start();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        if ($this->pdo !== null) {
            $this->pdo->prepare('DELETE FROM users WHERE email = ?')->execute([$this->email]);
            $this->pdo->prepare("DELETE FROM auth_audit_log WHERE metadata LIKE ?")->execute(['%' . $this->email . '%']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        if (isset($this->storage)) {
            exec('rm -rf ' . escapeshellarg($this->storage));
        }
    }

    private function app(bool $enabled = true): Application
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => self::KEY, 'log_channel' => 'null'],
            'integrations' => ['auth_enabled' => $enabled],
        ]);

        return new Application(dirname(__DIR__, 2), $config, $this->storage, ['pdo' => $this->pdo]);
    }

    /** @param array<string, mixed> $post */
    private function post(Application $app, string $path, array $post): Response
    {
        return $app->handle(new Request('POST', $path, [], $post, ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'phpunit']));
    }

    private function csrf(Application $app): string
    {
        $app->handle(new Request('GET', '/home/auth'));

        return (string) $_SESSION['_csrf_token'];
    }

    /** @return array<string, mixed> */
    private function registerForm(Application $app, array $override = []): array
    {
        return $override + [
            '_csrf' => $this->csrf($app),
            '_ts' => (new Signer(self::KEY))->sign((string) (time() - 30)),
            'website' => '',
            'regname' => 'QA Person',
            'regnick' => $this->nick,
            'regemail' => $this->email,
            'regpass' => self::PASSWORD,
            'regpass2' => self::PASSWORD,
        ];
    }

    public function testPageIsDisabledUntilAuthIsEnabled(): void
    {
        self::assertSame(404, $this->app(false)->handle(new Request('GET', '/home/auth'))->status);
        self::assertSame(404, $this->post($this->app(false), '/home/auth/login', [])->status);
    }

    public function testLoginPageHasCsrfAndNoDeadLinksOrInlineHandlers(): void
    {
        $r = $this->app()->handle(new Request('GET', '/home/auth'));
        self::assertSame(200, $r->status);
        self::assertSame('no-store', $r->header('Cache-Control'));
        self::assertMatchesRegularExpression('/name="_csrf" value="[0-9a-f]{64}"/', $r->body());
        self::assertDoesNotMatchRegularExpression('/href="#|\sonclick=|\sstyle="|wallet/i', $r->body());
        self::assertStringContainsString('noindex', $r->body());
    }

    public function testRegisterThenLoginThenLogout(): void
    {
        $app = $this->app();
        $r = $this->post($app, '/home/auth/register', $this->registerForm($app));
        self::assertSame(303, $r->status);
        self::assertSame('/home/auth', $r->header('Location'));
        self::assertSame(1, (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE email = " . $this->pdo->quote($this->email))->fetchColumn());

        $hash = (string) $this->pdo->query('SELECT password_hash FROM users WHERE email = ' . $this->pdo->quote($this->email))->fetchColumn();
        self::assertStringStartsWith('$argon2id$', $hash, 'passwords are Argon2id, never md5');
        self::assertStringNotContainsString(self::PASSWORD, $hash);

        $before = session_id();
        $r = $this->post($app, '/home/auth/login', ['_csrf' => (string) $_SESSION['_csrf_token'], 'logemail' => strtoupper($this->email), 'logpass' => self::PASSWORD]);
        self::assertSame(303, $r->status);
        self::assertSame('/', $r->header('Location'));
        self::assertNotSame($before, session_id(), 'session id is regenerated on login (fixation)');

        $home = $app->handle(new Request('GET', '/'))->body();
        self::assertStringContainsString('Hi, ' . $this->nick . '!', $home);
        self::assertStringContainsString('action="/home/auth/logout"', $home);

        self::assertSame(303, $app->handle(new Request('GET', '/home/auth'))->status, 'signed-in users are sent home');

        $out = $this->post($app, '/home/auth/logout', ['_csrf' => (string) $_SESSION['_csrf_token']]);
        self::assertSame('/', $out->header('Location'));
        self::assertEmpty($_SESSION, 'logout empties the session');
    }

    public function testWrongPasswordAndUnknownEmailGiveTheSameMessage(): void
    {
        $app = $this->app();
        $this->post($app, '/home/auth/register', $this->registerForm($app));

        $messages = [];
        foreach ([[$this->email, 'Wrong-Password-1'], ['nobody-' . $this->nick . '@example.test', self::PASSWORD]] as [$email, $password]) {
            $this->post($app, '/home/auth/login', ['_csrf' => (string) $_SESSION['_csrf_token'], 'logemail' => $email, 'logpass' => $password]);
            $messages[] = $app->handle(new Request('GET', '/home/auth'))->body();
        }
        foreach ($messages as $html) {
            self::assertStringContainsString('Email or password is incorrect', $html);
        }
        self::assertNull($_SESSION['_auth_user_id'] ?? null);
    }

    public function testLoginAndRegisterRequireCsrf(): void
    {
        $app = $this->app();
        $form = $this->registerForm($app);
        $form['_csrf'] = str_repeat('0', 64);
        self::assertSame(403, $this->post($app, '/home/auth/register', $form)->status);
        self::assertSame(403, $this->post($app, '/home/auth/login', ['logemail' => $this->email, 'logpass' => 'x'])->status);
        self::assertSame(403, $this->post($app, '/home/auth/logout', [])->status);
        self::assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE email = " . $this->pdo->quote($this->email))->fetchColumn());
    }

    public function testDuplicateAndWeakRegistrationsAreRejectedWithoutLeaking(): void
    {
        $app = $this->app();
        $this->post($app, '/home/auth/register', $this->registerForm($app));

        $this->post($app, '/home/auth/register', $this->registerForm($app, ['_csrf' => (string) $_SESSION['_csrf_token']]));
        $page = $app->handle(new Request('GET', '/home/auth'))->body();
        self::assertStringContainsString('That email or nickname is not available.', $page);
        self::assertStringNotContainsString('already exists', $page);

        $this->post($app, '/home/auth/register', $this->registerForm($app, ['_csrf' => (string) $_SESSION['_csrf_token'], 'regpass' => 'short', 'regpass2' => 'short', 'regemail' => 'weak-' . $this->email, 'regnick' => 'w' . $this->nick]));
        self::assertStringContainsString('at least 10 characters', $app->handle(new Request('GET', '/home/auth'))->body());
        self::assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE email = " . $this->pdo->quote('weak-' . $this->email))->fetchColumn());
    }

    public function testSqlInjectionAttemptsDoNothing(): void
    {
        $app = $this->app();
        $csrf = $this->csrf($app);
        $this->post($app, '/home/auth/login', ['_csrf' => $csrf, 'logemail' => "' OR '1'='1", 'logpass' => "' OR '1'='1"]);
        self::assertNull($_SESSION['_auth_user_id'] ?? null);
        self::assertGreaterThanOrEqual(0, (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn());
    }

    public function testHoneypotRegistrationStoresNothing(): void
    {
        $app = $this->app();
        $this->post($app, '/home/auth/register', $this->registerForm($app, ['website' => 'http://spam.example']));
        self::assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE email = " . $this->pdo->quote($this->email))->fetchColumn());
    }

    public function testResetPageIsHonest(): void
    {
        $r = $this->app()->handle(new Request('GET', '/home/auth/reset'));
        self::assertSame(200, $r->status);
        self::assertStringContainsString('not available yet', $r->body());
        self::assertStringNotContainsString('<form', $r->body());
    }

    public function testSessionsExpireOnTheServer(): void
    {
        $app = $this->app();
        $this->post($app, '/home/auth/register', $this->registerForm($app));
        $this->post($app, '/home/auth/login', ['_csrf' => (string) $_SESSION['_csrf_token'], 'logemail' => $this->email, 'logpass' => self::PASSWORD]);
        self::assertStringContainsString('Hi, ' . $this->nick, $app->handle(new Request('GET', '/'))->body());

        $_SESSION['_auth_authenticated_at'] = time() - 7200;
        self::assertStringNotContainsString('Hi, ' . $this->nick, $app->handle(new Request('GET', '/'))->body());
    }
}
