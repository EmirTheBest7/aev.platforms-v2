<?php

declare(strict_types=1);

namespace Core\Routing;

use Core\Helpers\View;

/** Public-safe error pages: no stack traces, paths, SQL or credentials, ever. */
final class ErrorController
{
    private const PAGES = [
        403 => ['Access denied', 'You do not have permission to view this page.'],
        404 => ['Page not found', 'The page you are looking for does not exist or has moved.'],
        405 => ['Method not allowed', 'That action is not supported for this address.'],
        410 => ['Gone', 'This page has been permanently removed.'],
        413 => ['Request too large', 'The request was larger than we accept.'],
        422 => ['Invalid request', 'The request could not be processed. Please check it and try again.'],
        429 => ['Too many requests', 'Please wait a little while before trying again.'],
        503 => ['Temporarily unavailable', 'This page cannot be shown right now. Please try again shortly.'],
        500 => ['Something went wrong', 'An unexpected error occurred on our side. Please try again shortly.'],
    ];

    public function __construct(private readonly View $view) {}

    /** JSON error for script callers; same public-safe messages, no internals. */
    public function renderJson(int $status): Response
    {
        $status = isset(self::PAGES[$status]) ? $status : 500;

        return Response::json(['ok' => false, 'error' => $status, 'message' => self::PAGES[$status][1]], $status)
            ->withHeader('Cache-Control', 'no-store');
    }

    public function render(int $status, string $path = '/'): Response
    {
        $status = isset(self::PAGES[$status]) ? $status : 500;
        [$title, $text] = self::PAGES[$status];

        $html = $this->view->render('errors/error', ['status' => $status, 'heading' => $title, 'text' => $text], [
            'title' => $status . ' · ' . $title . ' | ΛΞV',
            'path' => $path,
            'noindex' => true,
            'bodyClass' => 'page-error',
        ]);

        return (new Response($html, $status))->withHeader('Cache-Control', 'no-store');
    }
}
