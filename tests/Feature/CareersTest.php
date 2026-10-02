<?php

declare(strict_types=1);

namespace Tests\Feature;

use Core\Application;
use Core\Helpers\Config;
use Core\Routing\Request;
use Core\Routing\Response;
use PDO;
use PHPUnit\Framework\TestCase;
use Website\Careers\JobRepository;

final class CareersTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $this->pdo->exec('CREATE TABLE jobs (id INTEGER PRIMARY KEY AUTOINCREMENT, job_url TEXT UNIQUE, job_name TEXT, job_company TEXT, job_location TEXT, job_logo TEXT, job_salary TEXT, job_type TEXT, job_lead TEXT, job_desc TEXT, job_responsibilities TEXT, job_skills TEXT, published INTEGER DEFAULT 1)');
        $insert = $this->pdo->prepare('INSERT INTO jobs (job_url, job_name, job_company, job_location, job_logo, job_salary, job_type, job_lead, job_desc, job_responsibilities, job_skills, published) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $insert->execute(['dev', 'Developer <b>', 'ΛΞV - HR', 'Prague', '../../etc/passwd', '1', 'Freelance', 'Lead', "Line one\nLine two <script>alert(1)</script>", 'Do this. Then that.', 'Know a. Know b.', 1]);
        $insert->execute(['designer', 'Designer', 'NXR.EX', 'Remote', 'logo.png', '2', 'Full-time', 'Lead', 'Desc', 'Draw.', 'Eye.', 1]);
        $insert->execute(['hidden', 'Hidden role', 'NXR.EX', 'Remote', null, '2', 'Full-time', 'Lead', 'Desc', 'x', 'y', 0]);
    }

    private function get(string $path, ?PDO $pdo = null, array $query = []): Response
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config')->with([
            'app' => ['env' => 'testing', 'debug' => false, 'url' => 'https://aliev.test', 'key' => str_repeat('k', 40), 'log_channel' => 'null', 'log_level' => 'debug'],
        ]);
        $storage = sys_get_temp_dir() . '/aliev-careers-' . bin2hex(random_bytes(4));
        mkdir($storage, 0700, true);
        $app = new Application(dirname(__DIR__, 2), $config, $storage, ['pdo' => $pdo ?? $this->pdo]);

        return $app->handle(new Request('GET', $path, $query));
    }

    public function testListShowsOnlyPublishedJobsAndEscapesEverything(): void
    {
        $r = $this->get('/careers');
        self::assertSame(200, $r->status);
        $html = $r->body();
        self::assertStringContainsString('Developer &lt;b&gt;', $html);
        self::assertStringNotContainsString('Developer <b>', $html);
        self::assertStringNotContainsString('Hidden role', $html);
        self::assertStringContainsString('href="/careers/dev"', $html);
        self::assertStringContainsString('AEV.HR', $html, 'legacy filter label');
        self::assertStringContainsString("filter: [tag='c1']", $html);
    }

    public function testLogoNamesCannotEscapeTheLogoDirectory(): void
    {
        $html = $this->get('/careers')->body();
        self::assertStringNotContainsString('etc/passwd', $html);
        self::assertStringContainsString('/build/careers/images/job_icon.png', $html);
        self::assertStringContainsString('/build/careers/images/logo.png', $html);
    }

    public function testJobPageRendersEscapedContentAndRelatedJobs(): void
    {
        $r = $this->get('/careers/dev');
        self::assertSame(200, $r->status);
        $html = $r->body();
        self::assertStringContainsString('<title>ΛΞV | Developer &lt;b&gt;</title>', $html);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>alert(1)', $html);
        self::assertStringContainsString('<li>Do this.</li>', $html);
        self::assertStringContainsString('<li>Then that.</li>', $html);
        self::assertStringContainsString('href="/careers/designer"', $html, 'related jobs');
        self::assertStringNotContainsString('href="/careers/hidden"', $html);
        self::assertStringContainsString('rel="canonical" href="https://aliev.test/careers/dev"', $html);
    }

    /** @return iterable<string, array{string}> */
    public static function badSlugs(): iterable
    {
        yield 'unknown' => ['/careers/nope'];
        yield 'unpublished' => ['/careers/hidden'];
        yield 'quote' => ["/careers/a'b"];
        yield 'sql' => ["/careers/x'%20OR%20'1'='1"];
        yield 'too long' => ['/careers/' . str_repeat('a', 33)];
        yield 'dots' => ['/careers/..'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badSlugs')]
    public function testInvalidOrUnknownSlugIs404(string $path): void
    {
        $r = $this->get(rawurldecode($path));
        self::assertSame(404, $r->status);
        self::assertStringContainsString('Page not found', $r->body());
    }

    public function testSlugCannotInjectSql(): void
    {
        $repo = new JobRepository($this->pdo);
        self::assertNull($repo->findBySlug("dev' OR '1'='1"));
        self::assertNull($repo->findBySlug('dev; DROP TABLE jobs'));
        self::assertNotNull($repo->findBySlug('dev'));
        self::assertSame(3, (int) $this->pdo->query('SELECT COUNT(*) FROM jobs')->fetchColumn());
    }

    public function testTeamPageAndRouteOrder(): void
    {
        $r = $this->get('/careers/team');
        self::assertSame(200, $r->status);
        self::assertStringContainsString('Emir Aliev', $r->body());
        self::assertDoesNotMatchRegularExpression('/href="#"/', $r->body());
    }

    public function testEmptyListShowsAnEmptyState(): void
    {
        $empty = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $empty->exec('CREATE TABLE jobs (id INTEGER PRIMARY KEY, job_url TEXT, job_name TEXT, job_company TEXT, job_location TEXT, job_logo TEXT, job_salary TEXT, job_type TEXT, job_lead TEXT, job_desc TEXT, job_responsibilities TEXT, job_skills TEXT, published INTEGER)');
        $r = $this->get('/careers', $empty);
        self::assertSame(200, $r->status);
        self::assertStringContainsString('No open positions', $r->body());
    }

    public function testDatabaseFailureIsAGeneric503WithoutInternals(): void
    {
        $broken = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); // no jobs table
        $r = $this->get('/careers', $broken);
        self::assertSame(503, $r->status);
        self::assertStringNotContainsString('no such table', $r->body());
        self::assertStringNotContainsString('SQLSTATE', $r->body());
        self::assertStringContainsString('Temporarily unavailable', $r->body());
    }

    public function testLegacyUrlsRedirectInOneHop(): void
    {
        foreach (['/page/careers/list' => '/careers', '/page/careers' => '/careers', '/page/careers/team' => '/careers/team'] as $from => $to) {
            $r = $this->get($from);
            self::assertSame(301, $r->status, $from);
            self::assertSame($to, $r->header('Location'));
        }
        self::assertSame('/careers/dev', $this->get('/page/careers/desc', null, ['job_url' => 'dev'])->header('Location'));
        self::assertSame('/careers', $this->get('/page/careers/desc', null, ['job_url' => "x' OR 1=1"])->header('Location'));
    }

    public function testRouterMethodNotAllowedOnParameterRoute(): void
    {
        $config = Config::fromDirectory(dirname(__DIR__, 2) . '/config');
        $app = new Application(dirname(__DIR__, 2), $config->with(['app' => ['env' => 'testing', 'key' => str_repeat('k', 40), 'url' => 'https://aliev.test', 'log_channel' => 'null']]), sys_get_temp_dir(), ['pdo' => $this->pdo]);
        $r = $app->handle(new Request('POST', '/careers/dev'));
        self::assertSame(405, $r->status);
        self::assertSame('GET, HEAD', $r->header('Allow'));
    }
}
