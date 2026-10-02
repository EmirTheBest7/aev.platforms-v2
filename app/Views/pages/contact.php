<?php
/**
 * @var callable(mixed): string $e
 * @var string $csrf
 * @var string $ts
 * @var array<string, string> $services
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 * @var string|null $reference
 */
$oldServices = is_array($old['services'] ?? null) ? $old['services'] : [];
$val = static fn(string $k): string => is_string($old[$k] ?? null) ? $old[$k] : '';
?>
<section class="contact">
  <h1 class="page-title">Let's talk<span class="accent">.</span></h1>

<?php if ($reference !== null): ?>
  <div id="sent" class="notice notice--ok" role="status">
    <strong>Thank you — your request is in.</strong>
    <span>Reference <code><?= $e($reference) ?></code>. We'll reply by email.</span>
  </div>
<?php endif; ?>

<?php if ($errors !== []): ?>
  <div class="notice notice--error" role="alert">
    <strong>Please check the form.</strong>
    <ul>
<?php foreach ($errors as $message): ?>
      <li><?= $e($message) ?></li>
<?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

  <form id="form" class="form" method="post" action="/contact" novalidate>
    <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
    <input type="hidden" name="_ts" value="<?= $e($ts) ?>">

    <div class="field">
      <label for="name">Name</label>
      <input id="name" name="name" type="text" autocomplete="name" required minlength="2" maxlength="80" value="<?= $e($val('name')) ?>"<?= isset($errors['name']) ? ' aria-invalid="true"' : '' ?>>
    </div>

    <div class="field">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= $e($val('email')) ?>"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>>
    </div>

    <div class="field">
      <label for="company">Company <span class="optional">(optional)</span></label>
      <input id="company" name="company" type="text" autocomplete="organization" maxlength="120" value="<?= $e($val('company')) ?>">
    </div>

    <fieldset class="field field--services">
      <legend>What do you need?</legend>
<?php foreach ($services as $value => $label): ?>
      <label class="check">
        <input type="checkbox" name="services[]" value="<?= $e($value) ?>"<?= in_array($value, $oldServices, true) ? ' checked' : '' ?>>
        <span><?= $e($label) ?></span>
      </label>
<?php endforeach; ?>
    </fieldset>

    <div class="field">
      <label for="message">Project details</label>
      <textarea id="message" name="message" rows="6" required minlength="10" maxlength="4000"<?= isset($errors['message']) ? ' aria-invalid="true"' : '' ?>><?= $e($val('message')) ?></textarea>
    </div>

    <div class="hp" aria-hidden="true">
      <label for="website">Leave this field empty</label>
      <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
    </div>

    <button class="btn btn--primary" type="submit">Send request <span aria-hidden="true">→</span></button>
  </form>
</section>
