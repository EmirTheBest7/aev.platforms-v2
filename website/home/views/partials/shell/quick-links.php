<?php
/**
 * Quick links of the navigation rail (Instagram / YouTube / Telegram).
 *
 * @var callable(mixed): string $e
 * @var list<array<string, mixed>> $quickLinks
 */
$quickIcons = ['instagram' => 'instagram', 'youtube' => 'youtube', 'telegram' => 'telegram-alt'];
?>
    <ul class="Navbar-quickLinks">
<?php foreach ($quickLinks as $link): ?>
  <li><a href="<?= $e($link['href']) ?>" aria-label="<?= $e(ucfirst($link['key'])) ?>"<?= $link['external'] ? ' target="_blank" rel="noopener"' : ' data-soon="' . $e(ucfirst($link['key'])) . '"' ?>><i class="uil uil-<?= $e($quickIcons[$link['key']]) ?> icon-3d<?= $link['key'] === 'telegram' ? ' u-tg-size' : '' ?>" aria-hidden="true"></i></a></li>
<?php endforeach; ?>
</ul>
