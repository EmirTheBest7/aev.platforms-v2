<?php
/**
 * Top bar of the inner pages: a back/home button and the logo (same markup and classes as the original pages).
 *
 * @var callable(mixed): string $e
 * @var string $backHref
 * @var string $backIcon
 */
?>
  <nav class="Navbar">
    <a href="<?= $e($backHref) ?>" class="Toggle Navbar-toggle d-none d-sm-block" aria-label="Back">
      <i class="uil <?= $e($backIcon) ?>" aria-hidden="true"></i>
    </a>
    <img class="Navbar-brand u-pullRight Navbar-brand-mobile" alt="ΛLIΞV" src="/assets/brand/ALIEV.svg">
  </nav>
