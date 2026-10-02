<?php
/**
 * Component: hidden fields of a protected form (see Core\Security\FormGuard).
 *
 *   csrf (always) · ts + honeypot ('website', hidden off-screen) for public forms that use the full guard
 *
 * @var callable(mixed): string $e
 * @var string $csrf
 * @var string|null $ts   signed render time; omit for forms that only need CSRF (login, logout)
 */
?>
<input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
<?php if (($ts ?? null) !== null): ?>
<input type="hidden" name="_ts" value="<?= $e($ts) ?>">
<div class="hp" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
<?php endif; ?>
