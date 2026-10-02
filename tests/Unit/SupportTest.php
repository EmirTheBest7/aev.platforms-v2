<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Request;
use App\Security\ClientIp;
use App\Security\RateLimiter;
use App\Security\Signer;
use App\Services\LeadStore;
use App\Support\Env;
use App\Support\Logger;
use App\Support\SeasonalIcons;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SupportTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/aliev-test-' . bin2hex(random_bytes(4));
        mkdir($this->tmp, 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmp . '/*') ?: [] as $f) {
            is_dir($f) ? array_map('unlink', glob($f . '/*') ?: []) && @rmdir($f) : unlink($f);
        }
        @rmdir($this->tmp);
        Env::reset();
    }

    public function testEnvFileParsingAndRealEnvironmentWins(): void
    {
        file_put_contents($this->tmp . '/.env', "# c\nAPP_A=one\nAPP_B=\"two words\" # tail\nAPP_C='x'\nbad line\nlower=nope\nAPP_EMPTY=\n");
        Env::load($this->tmp . '/.env');

        self::assertSame('one', Env::get('APP_A'));
        self::assertSame('two words', Env::get('APP_B'));
        self::assertSame('x', Env::get('APP_C'));
        self::assertNull(Env::get('lower'));
        self::assertSame('fallback', Env::get('APP_EMPTY', 'fallback'));

        putenv('APP_A=from-process');
        self::assertSame('from-process', Env::get('APP_A'));
        putenv('APP_A');
    }

    public function testEnvTypedGetters(): void
    {
        Env::set('X_BOOL', 'true');
        Env::set('X_INT', '42');
        Env::set('X_BAD', 'abc');
        self::assertTrue(Env::bool('X_BOOL'));
        self::assertSame(42, Env::int('X_INT'));
        self::assertSame(7, Env::int('X_BAD', 7));
        self::assertFalse(Env::bool('X_MISSING'));
    }

    public function testSignerRoundTripAndTamperDetection(): void
    {
        $signer = new Signer(str_repeat('k', 32));
        $token = $signer->sign('1700000000');
        self::assertSame('1700000000', $signer->verify($token));
        self::assertNull($signer->verify('1700000001' . substr($token, 10)));
        self::assertNull($signer->verify('garbage'));
        self::assertNull((new Signer(str_repeat('z', 32)))->verify($token));
    }

    public function testSignerRejectsShortKey(): void
    {
        $this->expectException(\LogicException::class);
        new Signer('short');
    }

    public function testRateLimiterBlocksAfterLimitAndIsolatesSubjects(): void
    {
        $limiter = new RateLimiter($this->tmp . '/rl');
        self::assertTrue($limiter->hit('b', 'ip1', 2, 60));
        self::assertTrue($limiter->hit('b', 'ip1', 2, 60));
        self::assertFalse($limiter->hit('b', 'ip1', 2, 60));
        self::assertTrue($limiter->hit('b', 'ip2', 2, 60), 'other subject unaffected');
        self::assertTrue($limiter->hit('other', 'ip1', 2, 60), 'other bucket unaffected');
        self::assertSame([], glob($this->tmp . '/rl/*ip1*') ?: [], 'subjects are hashed on disk');
    }

    /** @param list<string> $trusted */
    #[DataProvider('ipCases')]
    public function testClientIpResolution(array $trusted, string $peer, ?string $xff, string $expected): void
    {
        $server = ['REMOTE_ADDR' => $peer] + ($xff !== null ? ['HTTP_X_FORWARDED_FOR' => $xff] : []);
        $request = new Request('GET', '/', [], [], $server);

        self::assertSame($expected, (new ClientIp($trusted))->resolve($request));
    }

    /** @return iterable<string, array{list<string>, string, ?string, string}> */
    public static function ipCases(): iterable
    {
        yield 'no trusted proxies ignores header' => [[], '203.0.113.9', '1.2.3.4', '203.0.113.9'];
        yield 'untrusted peer cannot spoof' => [['10.0.0.0/8'], '203.0.113.9', '1.2.3.4', '203.0.113.9'];
        yield 'trusted peer uses forwarded client' => [['10.0.0.0/8'], '10.1.2.3', '198.51.100.7', '198.51.100.7'];
        yield 'spoofed left entry is skipped' => [['10.0.0.1'], '10.0.0.1', '9.9.9.9, 198.51.100.7', '198.51.100.7'];
        yield 'invalid forwarded value falls back to peer' => [['10.0.0.1'], '10.0.0.1', 'not-an-ip', '10.0.0.1'];
        yield 'cidr boundary' => [['192.168.1.0/25'], '192.168.1.200', '198.51.100.7', '192.168.1.200'];
    }

    #[DataProvider('seasonCases')]
    public function testSeasonalIconsKeepLegacyBoundaries(string $date, string $folder): void
    {
        self::assertSame($folder, SeasonalIcons::folder(new \DateTimeImmutable($date)));
    }

    /** @return iterable<string, array{string, string}> */
    public static function seasonCases(): iterable
    {
        yield 'spring start' => ['2026-03-20', 'season1'];
        yield 'day before spring' => ['2026-03-19', 'season3'];
        yield 'summer start' => ['2026-06-20', 'season3'];
        yield 'autumn start' => ['2026-09-22', 'season1'];
        yield 'winter start' => ['2026-12-21', 'season3'];
        yield 'new year' => ['2026-01-01', 'season3'];
    }

    public function testLoggerRedactsSensitiveKeysAndTokenShapedStrings(): void
    {
        $logger = new Logger('file', $this->tmp . '/logs', 'debug');
        $fakeToken = '123456789:' . str_repeat('A', 35);
        $logger->info('t', ['token' => 'abc', 'email' => 'a@b.c', 'nested' => ['password' => 'p'], 'note' => "fail https://x/bot{$fakeToken}/send"]);

        $out = (string) file_get_contents((string) glob($this->tmp . '/logs/*.log')[0]);
        self::assertStringNotContainsString($fakeToken, $out);
        self::assertStringNotContainsString('a@b.c', $out);
        self::assertStringNotContainsString('"p"', $out);
        self::assertStringContainsString('[redacted]', $out);
    }

    public function testLeadReferenceFormatAndUniqueness(): void
    {
        $refs = array_map(static fn() => LeadStore::newReference(), range(1, 200));
        self::assertCount(200, array_unique($refs));
        foreach ($refs as $r) {
            self::assertMatchesRegularExpression('/^AEV-[0-9A-HJKMNP-TV-Z]{10}$/', $r);
        }
    }

    public function testLeadStoreWritesPrivateJsonLines(): void
    {
        $store = new LeadStore($this->tmp . '/leads');
        self::assertTrue($store->append(['reference' => 'AEV-X', 'name' => 'Zoë']));
        $file = (string) (glob($this->tmp . '/leads/*.jsonl')[0] ?? '');
        self::assertSame(['reference' => 'AEV-X', 'name' => 'Zoë'], json_decode(trim((string) file_get_contents($file)), true));
        self::assertSame('0600', substr(sprintf('%o', fileperms($file)), -4));
    }
}
