<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Support\View;

/** Mini-apps shown inside the main page's Widgets panel (framed by same-origin pages only). */
final class WidgetController
{
    public function __construct(private readonly View $view) {}

    public function clock(Request $request): Response
    {
        return $this->frame('widgets/clock', 'Clock', 'clock');
    }

    public function calculator(Request $request): Response
    {
        return $this->frame('widgets/calculator', 'Calculator', 'calculator');
    }

    private function frame(string $template, string $title, string $widget): Response
    {
        $html = $this->view->render($template, ['widget' => $widget], ['title' => $title, 'path' => '/widgets', 'noindex' => true], 'widget');

        return (new Response($html))->withCsp(['frame-ancestors' => "'self'"]);
    }
}
