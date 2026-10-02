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
 * @var array<string, string>|null $navbar arguments of components/navbar (leading, href, icon, label); null = no top bar
 */
$styles = ['/build/vendor/unicons/unicons-line.css'];
if (($navbar ?? null) !== null) {
    $styles[] = '/build/css/components/navbar.css';
}
$styles[] = '/build/css/components/button.css';
$styles[] = '/build/css/components/forms.css';
array_push($styles, ...($meta['styles'] ?? []));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?= $view->partial('components/head', ['styles' => $styles]) ?>
</head>
<body class="<?= $e($meta['bodyClass']) ?>">
<?php if (($navbar ?? null) !== null): ?>
<?= $view->partial('components/navbar', $navbar) ?>
<?php endif; ?>
<?= $content ?>
<?php foreach ($meta['scripts'] ?? [] as $src): ?>
  <script src="<?= $e($view->asset($src)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
