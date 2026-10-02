<?php

declare(strict_types=1);

namespace Website\Downloads;

use Core\Helpers\Config;
use Core\Helpers\View;
use Core\Routing\Request;
use Core\Routing\Response;

/** Downloads: brand logos, the wallpaper maker and documents, listed from config/downloads.php (static content). */
final class DownloadsController
{
    public function __construct(
        private readonly View $view,
        private readonly Config $config,
    ) {}

    public function index(Request $request): Response
    {
        $html = $this->view->render('downloads::downloads', [
            'logos' => (array) $this->config->get('downloads.logos', []),
            'wallpapers' => (array) $this->config->get('downloads.wallpapers', []),
            'docs' => (array) $this->config->get('downloads.docs', []),
            'navbar' => ['backHref' => '/', 'backIcon' => 'uil-estate'],
        ], [
            'title' => 'ΛΞV | Downloads',
            'description' => 'Logos, wallpapers and documents of Λ L I Ξ V for cooperation.',
            'path' => '/downloads',
            'bodyClass' => 'page-downloads',
            'styles' => ['/build/css/fonts/contact.css', '/build/vendor/bootstrap-icons/bootstrap-icons.css', '/build/css/core.css', '/build/downloads/css/downloads.css'],
            'scripts' => ['/build/vendor/jquery/jquery-3.1.0.min.js', '/build/js/ripple.js', '/build/downloads/js/downloads.js'],
        ], 'page');

        return new Response($html);
    }
}
