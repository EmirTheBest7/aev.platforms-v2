<?php

declare(strict_types=1);

namespace Tests\Feature;

use Core\Application;
use Core\Helpers\Config;
use Core\Routing\Request;
use PHPUnit\Framework\TestCase;

final class LegalTest extends TestCase
{
    protected function setUp(): void
    {
        // Headless PHPUnit run (as in SiteTest): sessions work without sending cookies/headers.
        ini_set('session.use_cookies', '0');
        ini_set('session.cache_limiter', '');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        session_id('t' . bin2hex(random_bytes(8)));
        session_start();
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    /** @param array<string, mixed> $legal */
    private function app(array $legal = []): Application
    {
        $overrides = ['app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => str_repeat('k', 40), 'log_channel' => 'null']];
        if ($legal !== []) {
            $overrides['legal'] = $legal;
        }
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with($overrides);

        return new Application(dirname(__DIR__, 2), $config, sys_get_temp_dir());
    }

    public function testWithoutContentBothPagesAdmitTheyArePlaceholdersAndStayOutOfSearch(): void
    {
        foreach (['/privacy' => 'Privacy Policy', '/imprint' => 'Imprint'] as $path => $title) {
            $response = $this->app([
                'operator' => ['name' => ['label' => 'Operator / controller', 'value' => '', 'required' => true]],
            ])->handle(new Request('GET', $path));
            $html = $response->body();

            self::assertSame(200, $response->status, $path);
            self::assertStringContainsString("<title>ΛΞV | {$title}</title>", $html);
            self::assertStringContainsString('Placeholder', $html);
            self::assertStringContainsString('To be provided by the owner.', $html);
            self::assertStringContainsString('noindex', $html);
        }
    }

    public function testNoCompanyDetailIsInventedByTheRepository(): void
    {
        $html = $this->app()->handle(new Request('GET', '/imprint'))->body();
        self::assertStringContainsString('Registered address', $html);
        self::assertSame(3, substr_count($html, 'To be provided by the owner.'), 'name, address and registration number are required and empty');
    }

    public function testProvidedContentIsRenderedEscapedAndIndexable(): void
    {
        $legal = [
            'operator' => [
                'name' => ['label' => 'Operator / controller', 'value' => 'Example <s>s.r.o.</s>', 'required' => true],
                'address' => ['label' => 'Registered address', 'value' => 'Test Street 1', 'required' => true],
                'registration_id' => ['label' => 'Company / registration number', 'value' => '000', 'required' => true],
            ],
            'privacy' => ['updated' => '2030-01-01', 'sections' => [['heading' => 'Who', 'body' => ['We are <b>them</b>.']]]],
        ];

        $imprint = $this->app($legal)->handle(new Request('GET', '/imprint'))->body();
        self::assertStringContainsString('Example &lt;s&gt;s.r.o.&lt;/s&gt;', $imprint);
        self::assertStringNotContainsString('Placeholder', $imprint);
        self::assertStringNotContainsString('noindex', $imprint);

        $privacy = $this->app($legal)->handle(new Request('GET', '/privacy'))->body();
        self::assertStringContainsString('We are &lt;b&gt;them&lt;/b&gt;.', $privacy);
        self::assertStringContainsString('Last updated: 2030-01-01', $privacy);
        self::assertStringNotContainsString('noindex', $privacy);
    }

    public function testMenuLinksToThePolicyInsteadOfAPendingStub(): void
    {
        $html = $this->app()->handle(new Request('GET', '/'))->body();
        self::assertStringContainsString('<a href="/privacy">Privacy Policy</a>', $html);
        self::assertStringNotContainsString('data-soon="Privacy Policy"', $html);
    }

    public function testNoTrailingSlashDuplicates(): void
    {
        self::assertSame(404, $this->app()->handle(new Request('GET', '/privacy/x'))->status);
    }
}
