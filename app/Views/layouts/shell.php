<?php
/**
 * The ALIEV.IO shell (legacy page/main chrome): preloader, notifications, spotlight, PWA modal,
 * user/launcher panel and the navbar rail, around the page's own <main>.
 *
 * @var string $content
 * @var array{title: string, description: string, path: string, noindex: bool, bodyClass: string} $meta
 * @var string $appUrl
 * @var callable(mixed): string $e
 * @var \App\Support\View $view
 * @var string $directoryJson
 * @var string $intergramJson
 */
$canonical = $appUrl . ($meta['path'] === '/' ? '/' : $meta['path']);
/** Cache-busting URL for first-party assets (long Cache-Control on /assets is safe because the URL changes with the file). */
$asset = static fn(string $path): string => $path . '?v=' . (@filemtime(dirname(__DIR__, 3) . '/public' . $path) ?: 1);
$icons = '/assets/icons/' . \App\Support\SeasonalIcons::folder(new \DateTimeImmutable());
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= $e($meta['title']) ?></title>
  <meta name="description" content="<?= $e($meta['description']) ?>">
  <meta name="author" content="Λ L I Ξ V Platforms">
  <meta name="theme-color" content="#000">
  <meta name="color-scheme" content="dark">
  <link rel="canonical" href="<?= $e($canonical) ?>">
  <meta property="og:locale" content="en-US">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Λ L I Ξ V">
  <meta property="og:title" content="<?= $e($meta['title']) ?>">
  <meta property="og:description" content="<?= $e($meta['description']) ?>">
  <meta property="og:url" content="<?= $e($canonical) ?>">
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?= $e($meta['title']) ?>">
  <meta name="twitter:description" content="<?= $e($meta['description']) ?>">
  <link rel="manifest" href="/manifest.webmanifest">
  <link rel="icon" href="<?= $e($icons) ?>/favicon.ico" sizes="any">
  <link rel="apple-touch-icon" href="<?= $e($icons) ?>/apple-touch-icon.png">
  <link rel="stylesheet" href="/assets/vendor/unicons/unicons-line.css?v=4.0.8">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/fonts.css')) ?>">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/main.css')) ?>">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/core.css')) ?>">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/widgets/3droom.css')) ?>">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/utilities.css')) ?>">
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/shell.css')) ?>">
  <script src="<?= $e($asset('/assets/vendor/jquery/jquery-3.1.0.min.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/vendor/underscore/underscore-1.8.3.min.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/vendor/three/three.r128.min.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/planet.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/sections.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/notify.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/spotlight.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/room.js')) ?>" defer></script>
  <script src="<?= $e($asset('/assets/js/home/app.js')) ?>" defer></script>
</head>
<body class="<?= $e($meta['bodyClass']) ?>">
  <a class="skip-link" href="#main">Skip to content</a>

<?= $view->partial('partials/shell/preloader') ?>

  <div class="aev-notifications" role="status" aria-live="polite" aria-relevant="additions"></div>

<?= $view->partial('partials/shell/spot') ?>
<?= $view->partial('partials/shell/pwa') ?>
<?= $view->partial('partials/shell/user-panel') ?>
<?= $view->partial('partials/shell/navbar') ?>

  <main id="main">
<?= $content ?>
  </main>

  <script type="application/json" id="aev-directory"><?= $directoryJson ?></script>
<?php if (($intergramJson ?? '') !== ''): ?>
  <script type="application/json" id="aev-intergram"><?= $intergramJson ?></script>
<?php endif; ?>
</body>
</html>
