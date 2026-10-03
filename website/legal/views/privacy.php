<?php
/**
 * Privacy Policy. Sections come from config/legal.php; a section without text shows the owner placeholder.
 *
 * @var callable(mixed): string $e
 * @var string $title
 * @var bool $complete
 * @var list<array{heading: string, body: list<string>}> $sections
 * @var string $updated
 */
?>
    <main id="main" class="legal">
      <h1 class="legal__title"><?= $e($title) ?></h1>
<?php if ($updated !== ''): ?>
      <p class="legal__meta">Last updated: <?= $e($updated) ?></p>
<?php endif; ?>
<?php if (!$complete): ?>
      <p class="legal__notice" role="note">Placeholder — the text of this policy has not been provided yet. The owner must supply it (config/legal.php) before launch.</p>
<?php endif; ?>
<?php foreach ($sections as $section): ?>
      <section class="legal__section">
        <h2><?= $e($section['heading']) ?></h2>
<?php if ($section['body'] === []): ?>
        <p class="legal__todo">To be provided by the owner.</p>
<?php else: ?>
<?php foreach ($section['body'] as $paragraph): ?>
        <p><?= $e($paragraph) ?></p>
<?php endforeach; ?>
<?php endif; ?>
      </section>
<?php endforeach; ?>
      <p class="legal__links"><a href="/imprint">Imprint</a> · <a href="/contact">Contact</a></p>
    </main>
