<!-- AEV App Menu & Panel -->
<div class="aev-user-panel">
  <button type="button" class="aev-apps-menu" aria-label="Apps" aria-haspopup="true" aria-expanded="false" aria-controls="aev-app-launcher"><i class="uil uil-apps" aria-hidden="true"></i></button>
  <button type="button" class="aev-user-badge" aria-label="Account" aria-haspopup="true" aria-expanded="false" aria-controls="aev-profile-options">
    <span class="circle">
      <img src="<?= $e($user['avatar']) ?>" alt="">
      <svg viewBox="0 0 100 100" xml:space="preserve" aria-hidden="true">
        <circle cx="50" cy="50" r="48"></circle>
      </svg>
    </span>
  </button>

  <div class="aev-profile-options" id="aev-profile-options" role="dialog" aria-label="Account">
    <div class="profile-items">
      <div class="saas">
        <h2 class="saas__title"><i class="uil uil-user" aria-hidden="true"></i> Account</h2>
        <div class="saas__block">
          <div class="saas__columns">
            <img src="<?= $e($user['avatar']) ?>" class="saas__user-avatar saas__user-avatar--lg" alt="">
            <div class="saas__user-info">
              <div class="saas__label"><?= $user['authenticated'] ? 'Signed in' : 'Guest' ?></div>
              <div class="saas__value saas__value--truncated"><?= $e($user['name']) ?></div>
              <div class="saas__label saas__label--plain saas__value--truncated"><?= $e($user['email']) ?></div>
            </div>
          </div>
          <hr class="saas__sep">
          <div class="saas__actions">
<?php if ($user['authenticated']): ?>
            <a class="saas__button" href="/home/_api/UI/">Dashboard <i class="uil uil-angle-right-b" aria-hidden="true"></i></a>
            <form method="post" action="/home/auth/logout" class="saas__form"><?= $view->partial('components/guard-fields', ['csrf' => $csrf, 'ts' => null]) ?><button type="submit" class="saas__button"><i class="uil uil-sign-out-alt u-881d0d7" aria-hidden="true"></i> Logout</button></form>
<?php else: ?>
            <a class="saas__button" href="<?= $e($signInHref) ?>"<?= $authEnabled ? '' : ' data-soon="Accounts"' ?>><i class="uil uil-sign-in-alt" aria-hidden="true"></i> Sign In</a>
<?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="aev-app-launcher" id="aev-app-launcher">
    <div class="apps">
      <ul class="first-set">
        <p class="aev-app-launcher-header"><i class="uil uil-apps" aria-hidden="true"></i> Web Apps</p>

<?php foreach ($apps as $app): ?>
        <li><a href="<?= $e($app['href']) ?>"<?= $app['external'] ? ' target="_blank" rel="noopener"' : '' ?><?= $app['action'] !== '' ? ' data-action="' . $e($app['action']) . '"' : '' ?><?= $app['soon'] ? ' data-soon="' . $e($app['name']) . '"' : '' ?>><img<?= $app['round'] ? ' class="u-round-avatar"' : '' ?> src="<?= $e($app['icon'] === 'avatar' ? $user['avatar'] : $app['icon']) ?>" alt=""><span><?= $e($app['name']) ?></span></a></li>
<?php endforeach; ?>

        <div class="aev-apps-footer">
          <div class="aev-apps-footer-container">
            <a class="aev-apps-footer-button" href="/">
              <span class="aev-apps-footer-content"><img src="/build/images/brand/A.svg" alt="" class="aev-apps-footer-logo"><span class="aev-apps-footer-text">aliev.io</span></span>
            </a>
            <button type="button" class="aev-apps-footer-button" data-action="open-spotlight">
              <span class="aev-apps-footer-content"><img src="/build/images/brand/A.svg" alt="" class="aev-apps-footer-logo"><span class="aev-apps-footer-text">All Apps</span></span>
            </button>
          </div>
        </div>
      </ul>
    </div>
  </div>
</div>
