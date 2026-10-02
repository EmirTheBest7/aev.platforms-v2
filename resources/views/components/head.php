<?php
/**
 * Component: document head — one implementation for every layout.
 *
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 * @var string $appUrl
 * @var array{title: string, description: string, path: string, noindex: bool, bodyClass: string} $meta
 * @var list<string> $styles   stylesheets (build URLs) in load order
 * @var string|null $extra     layout-specific tags (manifest, twitter card, …)
 */
$canonical = $appUrl . $meta['path'];
$icons = '/build/icons/' . \Core\Helpers\SeasonalIcons::folder(new \DateTimeImmutable());
?>
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
<?= $extra ?? '' ?>
  <link rel="icon" href="<?= $e($icons) ?>/favicon.ico" sizes="any">
  <link rel="apple-touch-icon" href="<?= $e($icons) ?>/apple-touch-icon.png">
<?php foreach ($styles as $href): ?>
  <link rel="stylesheet" href="<?= $e($view->asset($href)) ?>">
<?php endforeach; ?>
