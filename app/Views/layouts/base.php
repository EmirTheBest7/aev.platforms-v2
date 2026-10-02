<?php
/**
 * @var string $content
 * @var array{title: string, description: string, path: string, noindex: bool, bodyClass: string} $meta
 * @var string $appUrl
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 */
$canonical = $appUrl . ($meta['path'] === '/' ? '/' : $meta['path']);
$icons = '/assets/icons/' . \Core\Helpers\SeasonalIcons::folder(new \DateTimeImmutable());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?>
  <meta name="description" content="<?= $e($meta['description']) ?>">
<?php endif; ?>
  <meta name="color-scheme" content="dark">
  <meta name="theme-color" content="#000000">
<?php if ($meta['noindex']): ?>
  <meta name="robots" content="noindex,nofollow">
<?php else: ?>
  <link rel="canonical" href="<?= $e($canonical) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="ΛΞV">
  <meta property="og:title" content="<?= $e($meta['title']) ?>">
  <meta property="og:description" content="<?= $e($meta['description']) ?>">
  <meta property="og:url" content="<?= $e($canonical) ?>">
  <meta name="twitter:card" content="summary">
<?php endif; ?>
  <link rel="icon" href="<?= $e($icons) ?>/favicon.ico" sizes="any">
  <link rel="apple-touch-icon" href="<?= $e($icons) ?>/apple-touch-icon-180x180.png">
  <link rel="preload" href="/assets/fonts/Doto-latin.woff2" as="font" type="font/woff2" crossorigin>
  <link rel="stylesheet" href="/assets/css/site.css">
  <script src="/assets/js/nav.js" type="module"></script>
</head>
<body class="<?= $e($meta['bodyClass']) ?>">
  <a class="skip-link" href="#main">Skip to content</a>
<?= $view->partial('partials/rail', ['path' => $meta['path']]) ?>
  <main id="main">
<?= $content ?>
  </main>
</body>
</html>
