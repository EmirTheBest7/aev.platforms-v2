<?php
/**
 * @var callable(mixed): string $e
 * @var int $status
 * @var string $heading
 * @var string $text
 */
?>
<section class="error-page">
  <p class="error-page__code" aria-hidden="true"><?= $e($status) ?></p>
  <h1 class="page-title"><?= $e($heading) ?></h1>
  <p><?= $e($text) ?></p>
  <a class="btn" href="/">Back to home <span aria-hidden="true">→</span></a>
</section>
