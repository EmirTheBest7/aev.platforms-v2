<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Support\View;

/** Static public pages. */
final class PageController
{
    public function __construct(private readonly View $view) {}

    public function home(Request $request): Response
    {
        return new Response($this->view->render('pages/home', [], [
            'title' => 'ΛΞV | Digital studio',
            'description' => 'ALIEV.IO is a digital studio building websites, platforms and automation.',
            'path' => '/',
            'bodyClass' => 'page-home',
        ]));
    }
}
