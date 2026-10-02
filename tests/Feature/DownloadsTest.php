<?php

declare(strict_types=1);

namespace Tests\Feature;

use Core\Application;
use Core\Helpers\Config;
use Core\Routing\Request;
use PHPUnit\Framework\TestCase;

final class DownloadsTest extends TestCase
{
    private function app(): Application
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => str_repeat('k', 40), 'log_channel' => 'null'],
        ]);

        return new Application(dirname(__DIR__, 2), $config, sys_get_temp_dir());
    }

    public function testPageListsEveryConfiguredDownloadWithRealFiles(): void
    {
        $root = dirname(__DIR__, 2) . '/public';
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config');
        $response = $this->app()->handle(new Request('GET', '/downloads'));
        $html = $response->body();

        self::assertSame(200, $response->status);
        self::assertStringContainsString('<title>ΛΞV | Downloads</title>', $html);

        $files = [];
        foreach ((array) $config->get('downloads.logos') as $logo) {
            $files[] = $logo['file'];
            $files[] = $logo['preview'];
        }
        foreach ((array) $config->get('downloads.docs') as $doc) {
            if ($doc['file'] !== null) {
                $files[] = $doc['file'];
            }
        }
        foreach ((array) $config->get('downloads.wallpapers') as $wallpaper) {
            $files[] = rtrim($wallpaper['action']['href'], '/') . '/index.html';
            $files[] = $wallpaper['preview'];
        }
        foreach ($files as $file) {
            self::assertStringStartsWith('/', $file);
            self::assertStringNotContainsString('..', $file);
            self::assertFileExists($root . $file, "configured download {$file} must exist");
            self::assertStringContainsString('"' . $file . '"', $html . '"/downloads/wallpapers/create/index.html"', "{$file} is linked or shown");
        }
    }

    public function testMissingDocumentsAreHonestAndNotLinked(): void
    {
        $html = $this->app()->handle(new Request('GET', '/downloads'))->body();
        self::assertStringContainsString('Whitepaper.pdf', $html);
        self::assertStringNotContainsString('href="/downloads/docs/Whitepaper.pdf"', $html);
        self::assertSame(2, substr_count($html, 'Not available yet'));
        self::assertStringContainsString('href="/downloads/docs/ACS_System.pdf" download', $html);
    }

    public function testNoDeadLinksOrInlineHandlers(): void
    {
        $html = $this->app()->handle(new Request('GET', '/downloads'))->body();
        self::assertDoesNotMatchRegularExpression('/href="#"|href=""|javascript:|\sonclick=|\sstyle="/', $html);
    }

    public function testLegacyUrlRedirects(): void
    {
        $r = $this->app()->handle(new Request('GET', '/page/downloads'));
        self::assertSame(301, $r->status);
        self::assertSame('/downloads', $r->header('Location'));
    }
}
