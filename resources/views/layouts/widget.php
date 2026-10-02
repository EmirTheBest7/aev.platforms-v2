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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= $e($meta['title']) ?></title>
  <link rel="stylesheet" href="<?= $e($view->asset('/assets/css/widgets/' . $widget . '.css')) ?>">
</head>
<body>
<?= $content ?>
  <script type="module" src="<?= $e($view->asset('/assets/js/widgets/' . $widget . '.js')) ?>"></script>
</body>
</html>
