<?php

declare(strict_types=1);

namespace Tests\Feature;

use Core\Application;
use Core\Helpers\Config;
use Core\Routing\Request;
use PHPUnit\Framework\TestCase;

final class WorksTest extends TestCase
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

    public function testSliderIsRenderedFromConfigAndImagesExist(): void
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => str_repeat('k', 40), 'log_channel' => 'null'],
        ]);
        $items = (array) $config->get('works.items');
        self::assertSame(['Dreamers', 'Cerebro Blockchain', 'Cortex Browser', 'EROS'], array_column($items, 'name'));
        self::assertSame('right', $items[0]['position']);
        self::assertSame('left', $items[3]['position']);

        $html = (new Application(dirname(__DIR__, 2), $config, sys_get_temp_dir()))->handle(new Request('GET', '/'))->body();
        foreach ($items as $item) {
            self::assertFileExists(dirname(__DIR__, 2) . '/public' . $item['image']);
            self::assertStringContainsString('alt="' . $item['name'] . '"', $html);
            self::assertStringContainsString('<p class="slider--item-title">' . htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') . '</p>', $html);
        }
        self::assertStringContainsString('<li class="slider--item slider--item-right">', $html);
        self::assertStringContainsString('<li class="slider--item slider--item-left">', $html);
    }

    public function testEveryCardIsStillFlaggedForOwnerConfirmation(): void
    {
        $items = (array) Config::fromDirectory(dirname(__DIR__, 2) . '/config')->get('works.items');
        foreach ($items as $item) {
            self::assertFalse($item['confirmed'], $item['name'] . ' is unconfirmed until the owner says otherwise (config/works.php)');
        }
    }
}
