<?php
/**
 * Component: Navbar — the ALIEV.IO rail. One implementation for every page; pages choose only the leading control.
 *
 *   leading  'menu' → hamburger button (#toggle) that opens the menu panel      (main page)
 *            'link' → icon button that links somewhere (home, back)             (all other pages)
 *   href, icon, label   for 'link'
 *   brandHref  wrap the logo in a link (main page); null = plain logo (other pages)
 *   menu, quickLinks    optional HTML slots rendered inside the rail (main page panels, social quick links)
 * Styles: resources/css/components/navbar.css (loaded by the layouts).
 *
 * @var callable(mixed): string $e
 * @var string $leading
 * @var string|null $href
 * @var string|null $icon
 * @var string|null $label
 * @var string|null $brandHref
 * @var string|null $menu
 * @var string|null $quickLinks
 */
$brand = '<img class="Navbar-brand u-pullRight Navbar-brand-mobile" alt="' . (($brandHref ?? null) === null ? 'ΛLIΞV' : '') . '" src="/build/images/brand/ALIEV.svg">';
?>
  <nav class="Navbar">
<?php if ($leading === 'menu'): ?>
    <button type="button" id="toggle" class="Toggle Navbar-toggle" aria-label="Menu" aria-expanded="false" aria-controls="navbarCollapse"><span></span></button>
<?php else: ?>
    <a href="<?= $e($href) ?>" class="Toggle Navbar-toggle d-none d-sm-block" aria-label="<?= $e($label ?? 'Back') ?>">
      <i class="uil <?= $e($icon) ?>" aria-hidden="true"></i>
    </a>
<?php endif; ?>
<?php if (($brandHref ?? null) !== null): ?>

    <a href="<?= $e($brandHref) ?>" class="Navbar-brand-link" aria-label="ΛLIΞV — home"><?= $brand ?></a>
<?php else: ?>
    <?= $brand ?>

<?php endif; ?>
<?= $menu ?? '' ?>
<?= $quickLinks ?? '' ?>
  </nav>
