<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application;
use App\Http\Request;
use App\Http\Response;
use App\Services\DomainLookup;
use App\Services\Notifier\Notifier;
use App\Support\Config;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{
    private string $storage;
    /** @var list<string> */
    private array $sent = [];

    protected function setUp(): void
    {
        $this->storage = sys_get_temp_dir() . '/aliev-api-' . bin2hex(random_bytes(4));
        mkdir($this->storage, 0700, true);
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id('b' . bin2hex(random_bytes(8)));
        session_start();
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        exec('rm -rf ' . escapeshellarg($this->storage));
    }

    private function app(): Application
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => str_repeat('k', 40), 'log_channel' => 'null'],
            'integrations' => ['auth_enabled' => false],
        ]);
        $notifier = new class ($this->sent) implements Notifier {
            /** @param list<string> $sent */
            public function __construct(private array &$sent) {}

            public function send(string $text): bool
            {
                $this->sent[] = $text;

                return true;
            }
        };

        return new Application(dirname(__DIR__, 2), $config, $this->storage, [
            'notifier' => $notifier,
            'dns' => static fn(string $name): bool => $name === 'taken.example.com',
        ]);
    }

    /** @param array<string, string> $query @param array<string, string> $server */
    private function call(Application $app, string $method, string $path, array $query = [], array $server = []): Response
    {
        return $app->handle(new Request($method, $path, $query, [], $server + ['REMOTE_ADDR' => '203.0.113.20']));
    }

    public function testRootRedirectsToTheUiAndKeepsItsTrailingSlash(): void
    {
        $r = $this->call($this->app(), 'GET', '/home/_api/');
        self::assertSame(302, $r->status);
        self::assertSame('/home/_api/UI/', $r->header('Location'));
        self::assertSame(404, $this->call($this->app(), 'GET', '/home/_api')->status, 'no alias without the slash');
    }

    public function testPublicEndpointsReturnJsonWithoutCorsOrCaching(): void
    {
        foreach (['/home/_api/hello', '/home/_api/info', '/home/_api/me'] as $path) {
            $r = $this->call($this->app(), 'GET', $path);
            self::assertSame(200, $r->status, $path);
            self::assertStringContainsString('application/json', (string) $r->header('Content-Type'));
            self::assertSame('no-store', $r->header('Cache-Control'));
            self::assertNull($r->header('Access-Control-Allow-Origin'), 'no CORS');
        }
        self::assertSame(['authenticated' => false], json_decode($this->call($this->app(), 'GET', '/home/_api/me')->body(), true));
    }

    public function testUpdatesRequireASession(): void
    {
        self::assertSame(401, $this->call($this->app(), 'GET', '/home/_api/updates')->status);
    }

    public function testUnknownApiPathsAreNotDispatched(): void
    {
        foreach (['/home/_api/phpinfo', '/home/_api/login/admin:admin', '/home/_api/system', '/home/_api/index.php/func1'] as $path) {
            self::assertSame(404, $this->call($this->app(), 'GET', $path)->status, $path);
        }
    }

    public function testDomainToolValidatesAndNeverReflectsInput(): void
    {
        $app = $this->app();
        $ok = json_decode($this->call($app, 'GET', '/home/_api/tools/domain', ['name' => ' Taken.Example.com '])->body(), true);
        self::assertSame(['ok' => true, 'domain' => 'taken.example.com', 'hasRecords' => true], $ok);
        self::assertFalse(json_decode($this->call($app, 'GET', '/home/_api/tools/domain', ['name' => 'free.example.com'])->body(), true)['hasRecords']);

        foreach (['<script>alert(1)</script>.com', 'localhost', '127.0.0.1', 'a..com', '-a.com', str_repeat('a', 64) . '.com', 'exa mple.com', "x.com\n.evil"] as $bad) {
            $r = $this->call($app, 'GET', '/home/_api/tools/domain', ['name' => $bad]);
            self::assertSame(422, $r->status, $bad);
            self::assertStringNotContainsString('script', $r->body());
        }
    }

    public function testDomainToolIsRateLimited(): void
    {
        $app = $this->app();
        $statuses = [];
        for ($i = 0; $i < 22; $i++) {
            $statuses[] = $this->call($app, 'GET', '/home/_api/tools/domain', ['name' => 'a.example.com'])->status;
        }
        self::assertSame(429, end($statuses));
    }

    public function testNormalizeAcceptsIdnPunycodeAndRejectsJunk(): void
    {
        self::assertSame('xn--bcher-kva.example', DomainLookup::normalize('XN--bcher-kva.example'));
        self::assertNull(DomainLookup::normalize('example.c0m'));
        self::assertNull(DomainLookup::normalize(''));
    }

    public function testValentineNotificationIsServerSideCsrfProtectedAndFixed(): void
    {
        $app = $this->app();
        // no token → rejected, nothing sent
        self::assertSame(403, $this->call($app, 'POST', '/home/_api/valentine/yes')->status);
        self::assertSame([], $this->sent);

        $token = json_decode($this->call($app, 'GET', '/home/_api/csrf')->body(), true)['token'];
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);

        $r = $this->call($app, 'POST', '/home/_api/valentine/yes', [], ['HTTP_X_CSRF_TOKEN' => $token]);
        self::assertSame(200, $r->status);
        self::assertSame(['She said yes! 💌'], $this->sent, 'the message is fixed: client text never reaches the notifier');

        for ($i = 0; $i < 5; $i++) {
            $last = $this->call($app, 'POST', '/home/_api/valentine/yes', [], ['HTTP_X_CSRF_TOKEN' => $token])->status;
        }
        self::assertSame(429, $last);
        self::assertLessThanOrEqual(3, count($this->sent));
    }

    public function testWrongMethodIs405(): void
    {
        self::assertSame(405, $this->call($this->app(), 'GET', '/home/_api/valentine/yes')->status);
        self::assertSame(405, $this->call($this->app(), 'POST', '/home/_api/hello')->status);
    }

    public function testNoSecretsInTheBundle(): void
    {
        $root = dirname(__DIR__, 2) . '/public/home/_api';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!preg_match('/\.(html|js|css|json|txt)$/', $file->getFilename())) {
                continue;
            }
            $content = (string) file_get_contents($file->getPathname());
            self::assertDoesNotMatchRegularExpression('/\d{8,10}:AA[\w-]{30,}|api\.telegram\.org\/bot|pk\.eyJ/', $content, $file->getPathname());
        }
    }
}
