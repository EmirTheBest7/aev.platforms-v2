<?php
/**
 * Imprint / operator information. Values come from the LEGAL_* environment variables (config/legal.php).
 *
 * @var callable(mixed): string $e
 * @var string $title
 * @var bool $complete
 * @var list<array{label: string, value: string, required: bool}> $operator
 * @var string $email
 */
?>
    <main id="main" class="legal">
      <h1 class="legal__title"><?= $e($title) ?></h1>
<?php if (!$complete): ?>
      <p class="legal__notice" role="note">Placeholder — the operator details have not been provided yet. The owner must set the LEGAL_* environment variables before launch.</p>
<?php endif; ?>
      <dl class="legal__facts">
<?php foreach ($operator as $field): ?>
        <dt><?= $e($field['label']) ?></dt>
        <dd<?= $field['value'] === '' ? ' class="legal__todo"' : '' ?>><?= $field['value'] === '' ? 'To be provided by the owner.' : $e($field['value']) ?></dd>
<?php endforeach; ?>
<?php if ($email !== ''): ?>
        <dt>E-mail</dt>
        <dd><a href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a></dd>
<?php endif; ?>
      </dl>
      <p class="legal__links"><a href="/privacy">Privacy Policy</a> · <a href="/contact">Contact</a></p>
    </main>
