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

  <div class="aev-profile-options" id="aev-profile-options">
    <div class="profile-items">
      <ul class="first-set">
        <div class="aev-profile-card">
          <img src="<?= $e($user['avatar']) ?>" class="aev-avatar" alt="">
          <div class="aev-name"><?= $e($user['name']) ?></div>
          <div class="aev-email"><?= $e($user['email']) ?></div>
          <div class="aev-profile-buttons">
<?php if ($user['authenticated']): ?>
            <a class="aev-profile-button" href="/account">Dashboard</a>
            <form method="post" action="/account/logout" class="aev-profile-logout"><input type="hidden" name="_csrf" value="<?= $e($csrf) ?>"><button type="submit"><i class="uil uil-sign-out-alt u-881d0d7" aria-hidden="true"></i> Logout</button></form>
<?php else: ?>
            <a class="aev-profile-button" href="<?= $e($signInHref) ?>"<?= $authEnabled ? '' : ' data-soon="Accounts"' ?>>Sign In</a>
            <a class="aev-profile-button" href="<?= $e($signInHref) ?>"<?= $authEnabled ? '' : ' data-soon="Accounts"' ?>><i class="uil uil-sign-in-alt" aria-hidden="true"></i> Sign In</a>
<?php endif; ?>
          </div>
        </div>
      </ul>
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
              <span class="aev-apps-footer-content"><img src="/assets/brand/A.svg" alt="" class="aev-apps-footer-logo"><span class="aev-apps-footer-text">aliev.io</span></span>
            </a>
            <button type="button" class="aev-apps-footer-button" data-action="open-spotlight">
              <span class="aev-apps-footer-content"><img src="/assets/brand/A.svg" alt="" class="aev-apps-footer-logo"><span class="aev-apps-footer-text">All Apps</span></span>
            </button>
          </div>
        </div>
      </ul>
    </div>
  </div>
</div>
