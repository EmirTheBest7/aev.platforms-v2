<?php
/**
 * Contact page (port of the original page/contact): map + locations on the left, the form on the right.
 *
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 * @var string $csrf
 * @var string $ts
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 * @var string|null $reference
 * @var string $mapboxToken public Mapbox token from MAPBOX_TOKEN ('' = no map)
 * @var string $email
 */
$val = static fn(string $k): string => is_string($old[$k] ?? null) ? $old[$k] : '';
?>
  <main id="main">
    <div id='browser'>
      <div id='browser-bar'>
        <p>Contact Us</p>
        <span class='arrow entypo-resize-full'></span>
      </div>
      <div id='content'>
        <div id='left'>
          <div id="map" class="map"<?= $mapboxToken !== '' ? ' data-mapbox-token="' . $e($mapboxToken) . '"' : '' ?>></div>
          <ul id='location-bar'>
            <li>
              <a class='location' data-location='Prague' role='button' tabindex='0'>Prague 🇨🇿</a>
            </li>
            <li>
              <a class='location' data-location='Dubai' role='button' tabindex='0'>Dubai 🇦🇪</a>
            </li>
            <li>
              <a class='location' data-location='Kiev' role='button' tabindex='0'>Kiev 🇺🇦</a>
            </li>
            <li>
              <a class='location' data-location='London' role='button' tabindex='0'>London 🇬🇧</a>
            </li>
          </ul>
        </div>
        <div id='right'>
          <form method="POST" action="/contact">
            <p>Tell us about your project</p>
            <input type="hidden" name="_csrf" value="<?= $e($csrf) ?>">
            <input type="hidden" name="_ts" value="<?= $e($ts) ?>">
            <div class="hp" aria-hidden="true"><label for="website">Leave this field empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <input placeholder='Email' aria-label="Email" type='email' name="contact_email" required maxlength="254" autocomplete="email" value="<?= $e($val('email')) ?>"<?= isset($errors['email']) ? ' aria-invalid="true"' : '' ?>>
            <input placeholder='Subject' aria-label="Subject" type='text' name="contact_subject" required minlength="2" maxlength="120" value="<?= $e($val('subject')) ?>"<?= isset($errors['subject']) ? ' aria-invalid="true"' : '' ?>>
            <textarea placeholder='Message' aria-label="Message" rows='4' name="contact_message" required minlength="10" maxlength="4000"<?= isset($errors['message']) ? ' aria-invalid="true"' : '' ?>><?= $e($val('message')) ?></textarea>
<?php if ($reference !== null): ?>
            <p class="form-status form-status--ok" role="status">Message sent! Reference <?= $e($reference) ?></p>
<?php endif; ?>
<?php foreach ($errors as $error): ?>
            <p class="form-status form-status--error" role="alert"><?= $e($error) ?></p>
<?php endforeach; ?>
            <input type='submit' value='Send' aria-label="Send">
          </form>
          <hr>
          <p class='other entypo-mail'>
            <a href='mailto:<?= $e($email) ?>'><?= $e($email) ?></a>
          </p>
          <p class='other entypo-phone'>+420 736 455 744</p>
          <p class='other'>CIN: 14290863</p>
        </div>
      </div>

      <?= $view->partial('partials/contact/fish') ?>
    </div>
  </main>
