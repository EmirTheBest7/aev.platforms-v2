<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Application;
use App\Http\Request;
use App\Http\Response;
use App\Security\Signer;
use App\Services\Notifier\Notifier;
use App\Support\Config;
use PHPUnit\Framework\TestCase;

final class SiteTest extends TestCase
{
    private const KEY = 'test-key-test-key-test-key-test-key-0123';

    private string $storage;
    private SpyNotifier $notifier;

    protected function setUp(): void
    {
        $this->storage = sys_get_temp_dir() . '/aliev-feature-' . bin2hex(random_bytes(4));
        mkdir($this->storage, 0700, true);
        $this->notifier = new SpyNotifier();

        // Headless PHPUnit run: let sessions work without sending cookies/headers.
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id('t' . bin2hex(random_bytes(8)));
        session_start(); // already active, so SessionManager::start() is a no-op in this headless run
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        exec('rm -rf ' . escapeshellarg($this->storage));
    }

    /** @param array<string, array<string, mixed>> $overrides config overrides keyed by file (app, security, notify, …) */
    private function app(array $overrides = []): Application
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => self::KEY, 'log_channel' => 'null', 'log_level' => 'debug'],
            'security' => ['trusted_proxies' => [], 'force_https' => false],
            'notify' => ['driver' => 'log', 'timeout' => 2, 'telegram' => ['token' => '', 'chat_id' => '']],
        ])->with($overrides);

        return new Application(dirname(__DIR__, 2), $config, $this->storage, ['notifier' => $this->notifier]);
    }

    /** @param array<string, string> $server */
    private function get(Application $app, string $path, array $server = []): Response
    {
        return $app->handle(new Request('GET', $path, [], [], $server));
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, string> $server
     */
    private function post(Application $app, array $post, array $server = []): Response
    {
        return $app->handle(new Request('POST', '/contact', [], $post, $server + ['REMOTE_ADDR' => '203.0.113.5']));
    }

    /** @return array<string, mixed> a valid submission built from a real GET (token + signed timestamp) */
    private function validForm(Application $app): array
    {
        $this->get($app, '/contact');
        $csrf = (string) $_SESSION['_csrf_token'];

        return [
            '_csrf' => $csrf,
            '_ts' => (new Signer(self::KEY))->sign((string) (time() - 30)),
            'contact_email' => 'ada@example.com',
            'contact_subject' => 'A new website',
            'contact_message' => 'We would like a new website and some automation.',
            'website' => '',
        ];
    }

    public function testHomePageRendersWithSecurityHeadersAndSeo(): void
    {
        $r = $this->get($this->app(), '/');

        self::assertSame(200, $r->status);
        self::assertStringContainsString('<title>ΛΞV | Digital studio.</title>', $r->body());
        self::assertStringContainsString('<link rel="canonical" href="https://aliev.test/">', $r->body());
        self::assertStringContainsString('href="/contact"', $r->body());
        self::assertStringContainsString("script-src 'self'", (string) $r->header('Content-Security-Policy'));
        self::assertSame('nosniff', $r->header('X-Content-Type-Options'));
        self::assertSame('DENY', $r->header('X-Frame-Options'));
        self::assertStringNotContainsString('<script>', $r->body(), 'no inline scripts (CSP)');
        self::assertStringNotContainsString(' style="', $r->body(), 'no inline styles (CSP)');
    }

    public function testNoHardcodedDeadLinks(): void
    {
        foreach (['/', '/contact'] as $path) {
            $html = $this->get($this->app(), $path)->body();
            self::assertDoesNotMatchRegularExpression('/href="#"|href="#0"|href="#link"/', $html, $path);
        }
    }

    public function testHomePageContainsNoHesterGpt(): void
    {
        $html = $this->get($this->app(), '/')->body();
        self::assertStringNotContainsStringIgnoringCase('hester', $html);
        self::assertStringNotContainsStringIgnoringCase('avrora', $html);
        foreach (['slider--item-left', 'slider--item-center', 'slider--item-right'] as $slot) {
            self::assertSame(1, substr_count($html, $slot . '"'), "the carousel keeps exactly one $slot");
        }
    }

    public function testAssetsAreCacheBustedWithTheFileModificationTime(): void
    {
        $html = $this->get($this->app(), '/')->body();
        self::assertMatchesRegularExpression('#/assets/css/main\.css\?v=\d{6,}#', $html);
        self::assertMatchesRegularExpression('#/assets/js/home/app\.js\?v=\d{6,}#', $html);
    }

    public function testApiBundleKeepsItsTrailingSlashOtherPathsLoseIt(): void
    {
        // No route for the static bundle exists in PHP (Apache serves it): the router must answer 404 itself,
        // never redirect /home/_api/UI/ to /home/_api/UI (that would break the bundle's relative paths).
        $kept = $this->get($this->app(), '/home/_api/UI/');
        self::assertSame(404, $kept->status);
        self::assertNull($kept->header('Location'));

        $stripped = $this->get($this->app(), '/contact/');
        self::assertSame(301, $stripped->status);
        self::assertSame('/contact', $stripped->header('Location'));
    }

    public function testUnknownPathGivesSafe404(): void
    {
        $r = $this->get($this->app(), '/does-not-exist');
        self::assertSame(404, $r->status);
        self::assertStringContainsString('Page not found', $r->body());
        self::assertStringContainsString('noindex', $r->body());
        self::assertSame('no-store', $r->header('Cache-Control'));
    }

    public function testLegacyRoutes(): void
    {
        $app = $this->app();
        $r = $this->get($app, '/page/main/');
        self::assertSame(301, $r->status);
        self::assertSame('/', $r->header('Location'));
        self::assertSame(301, $this->get($app, '/page/contact')->status);
        // Preserved-but-not-yet-rebuilt products are NOT declared gone: honest 404, never 410.
        self::assertSame(404, $this->get($app, '/home/auth/')->status);
        self::assertSame(404, $this->get($app, '/home/timeline')->status);
    }

    public function testTrailingSlashIsCanonicalisedWithASingleHop(): void
    {
        $app = $this->app();
        $r = $this->get($app, '/contact/', ['QUERY_STRING' => 'a=1']);
        self::assertSame(301, $r->status);
        self::assertSame('/contact?a=1', $r->header('Location'));

        $legacy = $this->get($app, '/page/main/');
        self::assertSame('/', $legacy->header('Location'), 'legacy URL goes straight to the final target');

        self::assertSame(404, $this->get($app, '/nope/')->status, 'errors are not redirected');
    }

    public function testMethodNotAllowedAdvertisesAllow(): void
    {
        $r = $this->app()->handle(new Request('DELETE', '/contact'));
        self::assertSame(405, $r->status);
        self::assertSame('GET, HEAD, POST', $r->header('Allow'));
    }

    public function testHttpsRedirectOnlyHonoursTrustedProxy(): void
    {
        $app = $this->app(['app' => ['env' => 'production'], 'security' => ['force_https' => true, 'trusted_proxies' => ['10.0.0.1']]]);

        $plain = $this->get($app, '/contact', ['REMOTE_ADDR' => '203.0.113.9']);
        self::assertSame(301, $plain->status);
        self::assertSame('https://aliev.test/contact', $plain->header('Location'));

        $spoofed = $this->get($app, '/', ['REMOTE_ADDR' => '203.0.113.9', 'HTTP_X_FORWARDED_PROTO' => 'https']);
        self::assertSame(301, $spoofed->status, 'untrusted peer cannot claim https');

        $proxied = $this->get($app, '/', ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_PROTO' => 'https']);
        self::assertSame(200, $proxied->status);
        self::assertNotNull($proxied->header('Strict-Transport-Security'));
    }

    public function testContactFormRendersCsrfTimestampAndHoneypot(): void
    {
        $r = $this->get($this->app(), '/contact');
        self::assertSame(200, $r->status);
        self::assertSame('no-store', $r->header('Cache-Control'));
        self::assertMatchesRegularExpression('/name="_csrf" value="[0-9a-f]{64}"/', $r->body());
        self::assertStringContainsString('name="_ts"', $r->body());
        self::assertStringContainsString('name="website"', $r->body());
        foreach (['contact_email', 'contact_subject', 'contact_message'] as $field) {
            self::assertMatchesRegularExpression('/<(input|textarea)[^>]*aria-label="[^"]+"[^>]*name="' . $field . '"/', $r->body(), $field . ' is labelled');
        }
        self::assertDoesNotMatchRegularExpression('/data-mapbox-token/', $r->body(), 'no token configured: no map');
        self::assertStringNotContainsString('mapbox-gl.js', $r->body());
        self::assertStringNotContainsString('api.mapbox.com', (string) $r->header('Content-Security-Policy'));
    }

    public function testValidSubmissionIsPersistedNotifiedAndRedirected(): void
    {
        $app = $this->app();
        $r = $this->post($app, $this->validForm($app));

        self::assertSame(303, $r->status);
        self::assertSame('/contact', $r->header('Location'));
        self::assertCount(1, $this->notifier->sent);
        self::assertStringContainsString('ada@example.com', $this->notifier->sent[0]);
        self::assertStringContainsString('Subject: A new website', $this->notifier->sent[0]);

        $file = (string) glob($this->storage . '/leads/*.jsonl')[0];
        $row = json_decode((string) file_get_contents($file), true);
        self::assertMatchesRegularExpression('/^AEV-[0-9A-Z]{10}$/', $row['reference']);
        self::assertArrayNotHasKey('ip', $row, 'raw IP is never stored');
        self::assertSame(16, strlen($row['who']));

        $page = $this->get($app, '/contact');
        self::assertStringContainsString($row['reference'], $page->body(), 'success page shows the server-generated reference');
    }

    public function testClientCannotChooseTheReferenceNumber(): void
    {
        $app = $this->app();
        $form = $this->validForm($app) + ['order_number' => 'EVIL-1', 'reference' => 'EVIL-2'];
        $this->post($app, $form);

        $row = json_decode((string) file_get_contents((string) glob($this->storage . '/leads/*.jsonl')[0]), true);
        self::assertStringStartsWith('AEV-', $row['reference']);
        self::assertStringNotContainsString('EVIL', json_encode($row));
    }

    public function testCsrfFailureIsRejectedWithoutSideEffects(): void
    {
        $app = $this->app();
        $form = $this->validForm($app);
        $form['_csrf'] = str_repeat('0', 64);

        self::assertSame(403, $this->post($app, $form)->status);
        self::assertSame([], $this->notifier->sent);
        self::assertSame([], glob($this->storage . '/leads/*') ?: []);
    }

    public function testMissingCsrfIsRejected(): void
    {
        $app = $this->app();
        $form = $this->validForm($app);
        unset($form['_csrf']);
        self::assertSame(403, $this->post($app, $form)->status);
    }

    public function testHoneypotPretendsSuccessButStoresNothing(): void
    {
        $app = $this->app();
        $form = $this->validForm($app);
        $form['website'] = 'http://spam.example';
        $r = $this->post($app, $form);

        self::assertSame(303, $r->status);
        self::assertSame([], $this->notifier->sent);
        self::assertSame([], glob($this->storage . '/leads/*') ?: []);
    }

    public function testTooFastAndStaleFormsAreRejected(): void
    {
        $app = $this->app();
        $signer = new Signer(self::KEY);

        foreach ([time(), time() - 99999, 'forged'] as $stamp) {
            $form = $this->validForm($app);
            $form['_ts'] = is_int($stamp) ? $signer->sign((string) $stamp) : 'forged.value';
            $r = $this->post($app, $form);
            self::assertSame(303, $r->status);
            self::assertSame('/contact', $r->header('Location'));
        }
        self::assertSame([], $this->notifier->sent);
    }

    public function testValidationErrorsRedirectBackAndAreShownEscaped(): void
    {
        $app = $this->app();
        $form = $this->validForm($app);
        $form['contact_email'] = '"><script>alert(1)</script>';
        $form['contact_subject'] = '<b>Bob</b>';
        $r = $this->post($app, $form);

        self::assertSame('/contact', $r->header('Location'));
        $page = $this->get($app, '/contact')->body();
        self::assertStringContainsString('valid email', $page);
        self::assertStringNotContainsString('<script>alert(1)</script>', $page);
        self::assertSame([], $this->notifier->sent);
    }

    public function testMapboxTokenComesFromConfigurationAndWidensCspOnlyForThisPage(): void
    {
        $app = $this->app(['integrations' => ['mapbox_token' => 'pk.test-token-from-config']]);
        $r = $this->get($app, '/contact');
        self::assertStringContainsString('data-mapbox-token="pk.test-token-from-config"', $r->body());
        self::assertStringContainsString('/assets/vendor/mapbox-gl/mapbox-gl.js', $r->body());
        self::assertStringContainsString('https://api.mapbox.com', (string) $r->header('Content-Security-Policy'));
        self::assertStringNotContainsString('api.mapbox.com', (string) $this->get($app, '/')->header('Content-Security-Policy'));
        self::assertStringNotContainsString('pk.', (string) file_get_contents(dirname(__DIR__, 2) . '/public/assets/js/contact.js'), 'no token in the script');
    }

    public function testRateLimitReturns429(): void
    {
        $app = $this->app();
        $statuses = [];
        for ($i = 0; $i < 5; $i++) {
            $form = $this->validForm($app);
            $statuses[] = $this->post($app, $form)->status;
        }
        self::assertSame([303, 303, 303, 429, 429], $statuses);
        self::assertCount(3, $this->notifier->sent);
    }

    public function testOversizedBodyIsRejected(): void
    {
        $app = $this->app();
        $r = $this->post($app, $this->validForm($app), ['CONTENT_LENGTH' => '999999']);
        self::assertSame(413, $r->status);
    }

    public function testNotifierFailureDoesNotLoseTheLead(): void
    {
        $this->notifier->succeed = false;
        $app = $this->app();
        $r = $this->post($app, $this->validForm($app));

        self::assertSame('/contact', $r->header('Location'));
        self::assertCount(1, glob($this->storage . '/leads/*.jsonl') ?: []);
    }

    public function testUnexpectedExceptionNeverLeaksDetails(): void
    {
        $this->notifier->explode = true;
        $app = $this->app();
        $r = $this->post($app, $this->validForm($app));

        self::assertSame(500, $r->status);
        self::assertStringContainsString('Something went wrong', $r->body());
        self::assertStringNotContainsString('boom-secret-detail', $r->body());
        self::assertStringNotContainsString(__FILE__, $r->body());
    }
}

final class SpyNotifier implements Notifier
{
    /** @var list<string> */
    public array $sent = [];
    public bool $succeed = true;
    public bool $explode = false;

    public function send(string $text): bool
    {
        if ($this->explode) {
            throw new \RuntimeException('boom-secret-detail');
        }
        $this->sent[] = $text;

        return $this->succeed;
    }
}
