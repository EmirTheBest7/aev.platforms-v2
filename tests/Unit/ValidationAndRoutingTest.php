<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Validation\ContactValidator;
use PHPUnit\Framework\TestCase;

final class ValidationAndRoutingTest extends TestCase
{
    public function testValidInputIsAcceptedAndNormalised(): void
    {
        $r = (new ContactValidator())->validate(' ADA@Example.COM ', "  New \x00 site ", "Hello there,\r\nI need a site.\x07");

        self::assertSame([], $r['errors']);
        self::assertSame('ada@example.com', $r['data']['email']);
        self::assertSame('New site', $r['data']['subject']);
        self::assertSame("Hello there,\nI need a site.", $r['data']['message']);
    }

    public function testEveryRuleReportsAnError(): void
    {
        $r = (new ContactValidator())->validate('not-an-email', 'x', 'short');
        self::assertSame(['email', 'subject', 'message'], array_keys($r['errors']));
    }

    public function testLimitsAreEnforcedOnLength(): void
    {
        $v = new ContactValidator();
        self::assertArrayNotHasKey('message', $v->validate('a@b.co', 'Hi', str_repeat('x', 4000))['errors']);
        self::assertArrayHasKey('message', $v->validate('a@b.co', 'Hi', str_repeat('x', 4001))['errors']);
        self::assertArrayHasKey('subject', $v->validate('a@b.co', str_repeat('s', 121), str_repeat('x', 20))['errors']);
        self::assertArrayHasKey('email', $v->validate(str_repeat('a', 250) . '@b.co', 'Hi', str_repeat('x', 20))['errors']);
    }

    public function testRouterMatchesRedirectsAndMethodErrors(): void
    {
        $router = new Router();
        $router->get('/', static fn() => new Response('home'));
        $router->post('/form', static fn() => new Response('ok'));
        $router->redirects(['/page/main/' => ['/']]);

        self::assertSame('home', $router->dispatch(new Request('GET', '/'))->body());
        self::assertSame('home', $router->dispatch(new Request('HEAD', '/'))->body());

        $redirect = $router->dispatch(new Request('GET', '/page/main'));
        self::assertSame(301, $redirect->status);
        self::assertSame('/', $redirect->header('Location'));

        self::assertSame(405, $this->statusOf($router, new Request('GET', '/form')));
        self::assertSame(404, $this->statusOf($router, new Request('GET', '/nope')));
    }

    public function testParameterRoutesAreSingleSegmentAndExactRoutesWin(): void
    {
        $router = new Router();
        $router->get('/jobs/team', static fn() => new Response('team'));
        $router->get('/jobs/{slug}', static fn(Request $r, array $p) => new Response('job:' . $p['slug']));

        self::assertSame('job:dev', $router->dispatch(new Request('GET', '/jobs/dev'))->body());
        self::assertSame('team', $router->dispatch(new Request('GET', '/jobs/team'))->body());
        self::assertSame(404, $this->statusOf($router, new Request('GET', '/jobs/a/b')));
        self::assertSame(404, $this->statusOf($router, new Request('GET', '/jobs')));
        self::assertSame(405, $this->statusOf($router, new Request('POST', '/jobs/dev')));
    }

    public function testPathNormalisation(): void
    {
        self::assertSame('/', Request::normalizePath(''));
        self::assertSame('/contact', Request::normalizePath('/contact/'));
        self::assertSame('/a/b', Request::normalizePath('//a///b/'));
    }

    private function statusOf(Router $router, Request $request): int
    {
        try {
            return $router->dispatch($request)->status;
        } catch (HttpException $e) {
            return $e->status;
        }
    }
}
