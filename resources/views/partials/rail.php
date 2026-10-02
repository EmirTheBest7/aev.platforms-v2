<?php
/**
 * @var callable(mixed): string $e
 * @var string $path current request path
 */
$links = [
    '/' => 'Home',
    '/contact' => 'Contact',
];
?>
<header class="rail">
  <button class="rail__toggle" type="button" aria-expanded="false" aria-controls="site-menu" aria-label="Menu"><span></span></button>
  <a class="rail__brand" href="/" aria-label="ΛLIΞV — home"><img src="/assets/brand/ALIEV.svg" alt="" width="740" height="124"></a>
  <nav id="site-menu" class="rail__menu" aria-label="Main">
    <ul>
<?php foreach ($links as $href => $label): ?>
      <li><a href="<?= $e($href) ?>"<?= $path === $href ? ' aria-current="page"' : '' ?>><?= $e($label) ?></a></li>
<?php endforeach; ?>
    </ul>
  </nav>
</header>
