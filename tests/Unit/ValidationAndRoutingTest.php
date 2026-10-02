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
        $r = (new ContactValidator())->validate("  Ada \x00 Lovelace ", ' ADA@Example.COM ', '', ['web', 'bogus', 'web'], "Hello there,\r\nI need a site.\x07");

        self::assertSame([], $r['errors']);
        self::assertSame('Ada Lovelace', $r['data']['name']);
        self::assertSame('ada@example.com', $r['data']['email']);
        self::assertSame(['web'], $r['data']['services'], 'unknown + duplicate services dropped');
        self::assertSame("Hello there,\nI need a site.", $r['data']['message']);
    }

    public function testEveryRuleReportsAnError(): void
    {
        $r = (new ContactValidator())->validate('A', 'not-an-email', str_repeat('c', 121), [], 'short');
        self::assertSame(['name', 'email', 'company', 'message'], array_keys($r['errors']));
    }

    public function testLimitsAreEnforcedOnLength(): void
    {
        $v = new ContactValidator();
        self::assertArrayNotHasKey('message', $v->validate('Ada', 'a@b.co', '', [], str_repeat('x', 4000))['errors']);
        self::assertArrayHasKey('message', $v->validate('Ada', 'a@b.co', '', [], str_repeat('x', 4001))['errors']);
        self::assertArrayHasKey('email', $v->validate('Ada', str_repeat('a', 250) . '@b.co', '', [], str_repeat('x', 20))['errors']);
    }

    public function testRouterMatchesRedirectsGoneAndMethodErrors(): void
    {
        $router = new Router();
        $router->get('/', static fn() => new Response('home'));
        $router->post('/form', static fn() => new Response('ok'));
        $router->redirects(['/page/main/' => ['/']]);
        $router->gone(['/home']);

        self::assertSame('home', $router->dispatch(new Request('GET', '/'))->body());
        self::assertSame('home', $router->dispatch(new Request('HEAD', '/'))->body());

        $redirect = $router->dispatch(new Request('GET', '/page/main'));
        self::assertSame(301, $redirect->status);
        self::assertSame('/', $redirect->header('Location'));

        foreach (['/home', '/home/auth/login', '/home/_api/UI'] as $path) {
            self::assertSame(410, $this->statusOf($router, new Request('GET', $path)), $path);
        }
        self::assertSame(404, $this->statusOf($router, new Request('GET', '/homepage')), 'prefix match respects path boundary');
        self::assertSame(405, $this->statusOf($router, new Request('GET', '/form')));
        self::assertSame(404, $this->statusOf($router, new Request('GET', '/nope')));
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
