<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Support\Config;
use App\Support\View;

/** Downloads: brand logos, the wallpaper maker and documents, listed from config/downloads.php (static content). */
final class DownloadsController
{
    public function __construct(
        private readonly View $view,
        private readonly Config $config,
    ) {}

    public function index(Request $request): Response
    {
        $html = $this->view->render('pages/downloads', [
            'logos' => (array) $this->config->get('downloads.logos', []),
            'wallpapers' => (array) $this->config->get('downloads.wallpapers', []),
            'docs' => (array) $this->config->get('downloads.docs', []),
            'navbar' => ['backHref' => '/', 'backIcon' => 'uil-estate'],
        ], [
            'title' => 'ΛΞV | Downloads',
            'description' => 'Logos, wallpapers and documents of Λ L I Ξ V for cooperation.',
            'path' => '/downloads',
            'bodyClass' => 'page-downloads',
            'styles' => ['/assets/css/contact-fonts.css', '/assets/vendor/bootstrap-icons/bootstrap-icons.css', '/assets/css/core.css', '/assets/css/downloads.css'],
            'scripts' => ['/assets/vendor/jquery/jquery-3.1.0.min.js', '/assets/js/ripple.js', '/assets/js/downloads.js'],
        ], 'page');

        return new Response($html);
    }
}
