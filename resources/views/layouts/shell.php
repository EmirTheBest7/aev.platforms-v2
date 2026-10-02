<?php
/**
 * The ALIEV.IO shell (legacy page/main chrome): preloader, notifications, spotlight, PWA modal,
 * user/launcher panel and the navbar rail, around the page's own <main>.
 *
 * @var string $content
 * @var array{title: string, description: string, path: string, noindex: bool, bodyClass: string} $meta
 * @var string $appUrl
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 * @var string $directoryJson
 * @var string $intergramJson
 */
$styles = [
    '/build/vendor/unicons/unicons-line.css',
    '/build/css/fonts/base.css',
    '/build/home/css/main.css',
    '/build/css/components/navbar.css',
    '/build/css/core.css',
    '/build/css/components/button.css',
    '/build/css/components/forms.css',
    '/build/home/css/preloader.css',
    '/build/home/css/widgets/3droom.css',
    '/build/home/css/utilities.css',
    '/build/home/css/shell.css',
    '/build/home/css/profile-widget.css',
];
$extra = <<<'HTML'
  <meta name="author" content="Λ L I Ξ V Platforms">
  <meta name="color-scheme" content="dark">
  <meta property="og:locale" content="en-US">
  <meta name="twitter:card" content="summary">
  <link rel="manifest" href="/manifest.webmanifest">
HTML;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<?= $view->partial('components/head', ['styles' => $styles, 'extra' => $extra . "\n" . '  <meta name="twitter:title" content="' . $e($meta['title']) . '">' . "\n" . '  <meta name="twitter:description" content="' . $e($meta['description']) . '">']) ?>
  <script src="<?= $e($view->asset('/build/vendor/jquery/jquery-3.1.0.min.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/vendor/underscore/underscore-1.8.3.min.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/vendor/three/three.r128.min.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/planet.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/sections.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/notify.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/spotlight.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/room.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/js/ripple.js')) ?>" defer></script>
  <script src="<?= $e($view->asset('/build/home/js/home/app.js')) ?>" defer></script>
</head>
<body class="<?= $e($meta['bodyClass']) ?>">
  <a class="skip-link" href="#main">Skip to content</a>

<?= $view->partial('home::partials/shell/preloader') ?>

  <div class="aev-notifications" role="status" aria-live="polite" aria-relevant="additions"></div>

<?= $view->partial('home::partials/shell/spot') ?>
<?= $view->partial('home::partials/shell/pwa') ?>
<?= $view->partial('home::partials/shell/user-panel') ?>
<?= $view->partial('components/navbar', ['leading' => 'menu', 'brandHref' => '/', 'menu' => $view->partial('home::partials/shell/menu'), 'quickLinks' => $view->partial('home::partials/shell/quick-links')]) ?>

  <main id="main">
<?= $content ?>
  </main>

  <script type="application/json" id="aev-directory"><?= $directoryJson ?></script>
<?php if (($intergramJson ?? '') !== ''): ?>
  <script type="application/json" id="aev-intergram"><?= $intergramJson ?></script>
<?php endif; ?>
</body>
</html>
