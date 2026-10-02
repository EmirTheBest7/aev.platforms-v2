<?php
/**
 * Layout of the inner pages (Careers, Contact, Downloads, Auth): the page brings its own legacy stylesheet(s)
 * and script(s) through $meta['styles'] / $meta['scripts']; the home-page chrome is not loaded.
 *
 * @var string $content
 * @var array{title: string, description: string, path: string, noindex: bool, bodyClass: string, styles?: list<string>, scripts?: list<string>} $meta
 * @var string $appUrl
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 * @var array<string, string>|null $navbar back-link of the top bar (backHref, backIcon); null = none
 */
$canonical = $appUrl . $meta['path'];
$icons = '/assets/icons/' . \Core\Helpers\SeasonalIcons::folder(new \DateTimeImmutable());
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?= $e($meta['title']) ?></title>
<?php if ($meta['description'] !== ''): ?>
  <meta name="description" content="<?= $e($meta['description']) ?>">
<?php endif; ?>
  <meta name="theme-color" content="#000">
<?php if ($meta['noindex']): ?>
  <meta name="robots" content="noindex,nofollow">
<?php else: ?>
  <link rel="canonical" href="<?= $e($canonical) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="Λ L I Ξ V">
  <meta property="og:title" content="<?= $e($meta['title']) ?>">
  <meta property="og:description" content="<?= $e($meta['description']) ?>">
  <meta property="og:url" content="<?= $e($canonical) ?>">
<?php endif; ?>
  <link rel="icon" href="<?= $e($icons) ?>/favicon.ico" sizes="any">
  <link rel="apple-touch-icon" href="<?= $e($icons) ?>/apple-touch-icon.png">
  <link rel="stylesheet" href="<?= $e($view->asset('/assets/vendor/unicons/unicons-line.css')) ?>">
<?php foreach ($meta['styles'] ?? [] as $href): ?>
  <link rel="stylesheet" href="<?= $e($view->asset($href)) ?>">
<?php endforeach; ?>
</head>
<body class="<?= $e($meta['bodyClass']) ?>">
<?php if (($navbar ?? null) !== null): ?>
<?= $view->partial('partials/page/navbar', $navbar) ?>
<?php endif; ?>
<?= $content ?>
<?php foreach ($meta['scripts'] ?? [] as $src): ?>
  <script src="<?= $e($view->asset($src)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
