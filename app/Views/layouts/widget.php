<?php
/**
 * Minimal frame for the mini-apps shown inside the main page's Widgets panel.
 *
 * @var string $content
 * @var array{title: string} $meta
 * @var string $widget
 * @var callable(mixed): string $e
 */
?>
<?php
/** Cache-busting URL for first-party assets. */
$asset = static fn(string $path): string => $path . '?v=' . (@filemtime(dirname(__DIR__, 3) . '/public' . $path) ?: 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= $e($meta['title']) ?></title>
  <link rel="stylesheet" href="<?= $e($asset('/assets/css/widgets/' . $widget . '.css')) ?>">
</head>
<body>
<?= $content ?>
  <script type="module" src="<?= $e($asset('/assets/js/widgets/' . $widget . '.js')) ?>"></script>
</body>
</html>
