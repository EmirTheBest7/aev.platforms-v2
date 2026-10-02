<?php
/**
 * @var callable(mixed): string $e
 * @var array<string, mixed> $user
 * @var bool $authEnabled
 * @var string $signInHref
 * @var list<array<string, mixed>> $quickLinks
 * @var list<array<string, mixed>> $mobileLinks
 * @var string $weekday
 * @var string $dayMonth
 * @var string $docsHref
 * @var bool $docsSoon
 */
$socialIcons = ['facebook' => 'facebook-f', 'twitter' => 'twitter', 'instagram' => 'instagram'];
$quickIcons = ['instagram' => 'instagram', 'youtube' => 'youtube', 'telegram' => 'telegram-alt'];
// A control whose destination is not available yet keeps its place and shows the designed pending notification.
$pending = static fn(string $name): string => ' href="/" data-soon="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"';
$docsAttrs = static fn(string $name): string => $docsSoon
    ? $pending($name)
    : ' href="' . htmlspecialchars($docsHref, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener"';
?>
<nav class="Navbar">
    <button type="button" id="toggle" class="Toggle Navbar-toggle" aria-label="Menu" aria-expanded="false" aria-controls="navbarCollapse"><span></span></button>

    <a href="/" class="Navbar-brand-link" aria-label="ΛLIΞV — home"><img class="Navbar-brand u-pullRight Navbar-brand-mobile" alt="" src="/assets/brand/ALIEV.svg"></a>

    <div id="navbarCollapse" class="Navbar-menu">

      <div id="login1" class="switch-group">
        
        <ul class="Navbar-menu-major">
          <li class="u-7a88889">
            <input type="text" class="m-spotlight-search" placeholder="Re:Search" aria-label="Search" aria-haspopup="dialog" autocomplete="off">
          </li>
          <li class="mod-buttons flex">
            <button type="button" class="ext-sign-in-btn" data-panel="settings">Settings</button>
            <button type="button" class="ext-sign-in-btn" data-panel="widgets"> Widgets</button>
          </li>
          <li><hr></li>
          
          <li>
            <ul id="aDHieSVT" class="aDHieSVT">

              <li>
                <button type="button" class="aDHieSVT-link" aria-expanded="false">Explore <i class="uil uil-plus" aria-hidden="true"></i></button>
                <ul class="aDHieSVT-submenu">
                  <li class="u-e839eea">
                      <a class="aDHieSVT-card" href="/#hire" data-section="hire">
                        <div class="aDHieSVT-wrapper">
                          <div class="u-c27f5cd">
                            <span class="uil uil-fire u-9aa9c87">
                            </span>
                          </div>
                          <div><h3>Services</h3></div>
                        </div>
                        <p class="u-1a867bf">Explore possibilities</p>
                      </a>
                  </li>
                  <li class="u-e839eea">
                      <a class="aDHieSVT-card" href="/#works" data-section="works">
                        <div class="aDHieSVT-wrapper">
                          <div class="u-c27f5cd">
                            <span class="uil uil-compress u-9aa9c87">
                            </span>
                          </div>
                          <div><h3>Portfolio</h3></div>
                        </div>
                        <p class="u-1a867bf">Our collection</p>
                      </a>
                  </li>
                  <li class="u-e839eea">
                      <a class="aDHieSVT-card" href="/" data-action="open-launcher">
                        <div class="aDHieSVT-wrapper">
                          <div class="u-c27f5cd">
                            <span class="uil uil-apps u-9aa9c87">
                            </span>
                          </div>
                          <div><h3>Ecosystem</h3></div>
                        </div>
                        <p class="u-1a867bf">Explore the ΛΞV ecosystem</p>
                      </a>
                  </li>
                </ul>
              </li>
              
              <li>
                <button type="button" class="aDHieSVT-link" aria-expanded="false">Learn <i class="uil uil-plus" aria-hidden="true"></i></button>
                <ul class="aDHieSVT-submenu">
                  <li><a class="uil uil-corner-down-right u-b2dd4f5"<?= $pending('Journal') ?>>Journal</a></li>
                </ul>
              </li>
              <li>
                <button type="button" class="aDHieSVT-link" aria-expanded="false">Build <i class="uil uil-plus" aria-hidden="true"></i></button>
                <ul class="aDHieSVT-submenu">
                  <li><a class="uil uil-bolt-alt u-b2dd4f5"<?= $docsAttrs('Docs') ?>>Quickstart</a></li>
                  <li><a class="uil uil-book-open u-b2dd4f5"<?= $docsAttrs('Docs') ?>>Documentation</a></li>
                  <li><a class="uil uil-code-branch u-b2dd4f5"<?= $pending('CLI') ?>>CLI</a></li>
                </ul>
              </li>
            </ul>
          </li>

        </ul>
        
        <div class="Navbar-menu-minor">
          <ul>
            <li><a<?= $pending('Store') ?>>Store</a></li>
            <li><a href="/careers">Careers</a></li>
            <li><a href="/downloads">Downloads</a></li>
          </ul>
          <ul>
            <li><a<?= $pending('Privacy Policy') ?>>Privacy Policy</a></li>
            <li><a<?= $pending('Investor Relations') ?>>Investor Relations</a></li>
            <li><a href="/contact">Contact</a></li>
            <li>
              <a class="u-log-in" href="<?= $e($signInHref) ?>"<?= $authEnabled ? '' : ' data-soon="Accounts"' ?>><span class="ripple-button"><?= $user['authenticated'] ? 'Dashboard' : 'Log In' ?></span></a>
            </li>
          </ul>
        
          <ul class="Navbar-menu-social u-Navbar-hidden@sm-up">
<?php foreach ($mobileLinks as $link): ?>
            <li>
              <a class="SocialLink" href="<?= $e($link['href']) ?>"<?= $link['external'] ? ' target="_blank" rel="noopener"' : ' data-soon="' . $e(ucfirst($link['key'])) . '"' ?>>
                <i class="uil uil-<?= $e($socialIcons[$link['key']]) ?> SocialLink-icon" aria-hidden="true"></i>
                <span class="SocialLink-text"><?= $e(ucfirst($link['key'])) ?></span>
              </a>
            </li>
<?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div id="register1" class="ext-sign-in switch-group">
        <div id="ext-sign-in-content" class="text-center">

          
          <button type="button" class="ext-sign-in-back btn u-afdedb0" data-panel="menu"> <i class="uil uil-angle-left-b" aria-hidden="true"></i> Back</button>

          <div class="widget__time u-b7ae7c2">
            <h1 id="widget_weekday"><?= $e($weekday) ?></h1>
            <h1 id="widget_daymonth"><?= $e($dayMonth) ?></h1>
          </div>

          <div class="widget" role="group" aria-label="Clock widget">
            <div class="widget__bar"><i class="uil uil-clock"></i> Clock <i class="uil uil-info-circle"></i></div>
            <div class="widget__content-frame">
              <div class="widget__content">
                <iframe data-src="/widgets/clock" title="ΛΞV Clock" loading="lazy"></iframe>
              </div>
            </div>
          </div>

          <div class="widget" role="group" aria-label="Calculator widget">
            <div class="widget__bar"><i class="uil uil-calculator"></i> Calculator <i class="uil uil-info-circle"></i></div>
            <div class="widget__content-frame">
              <div class="widget__content">
                <iframe data-src="/widgets/calculator" title="ΛΞV Calculator" loading="lazy"></iframe>
              </div>
            </div>
          </div>
          
          
        </div>
      </div>

      <div id="settings1" class="ext-sign-in switch-group">
        <div id="ext-settings-content" class="text-center">

          <button type="button" class="ext-sign-in-back btn u-d3624b7" data-panel="menu">Back <i class="uil uil-angle-right-b" aria-hidden="true"></i></button>

          <div class="settings">
          <h1 class="u-9999d25">Settings <i class="uil uil-setting"></i></h1>
            <span class="settings__title field-title">Functional key</span>
            <div class="result__viewbox" id="result" contenteditable="true" role="textbox" aria-label="Functional key">{{ ... }}</div>
            <button type="button" class="result__viewbox__btn" data-action="functional-key">Check</button>

            <span class="settings__title field-title">Language</span>
            <div class="u-3cbd017">
              <select name="language" id="language" aria-label="Language">
                <option value="en">🇬🇧 English</option>
                <option value="cz">🇨🇿 Czech</option>
                <option value="ua">🇺🇦 Ukrainian</option>
                <option value="ru">🇷🇺 Russian</option>
                <option value="qt">🇺🇳 Crimean Tatar</option>
              </select>
            </div>

            <span class="settings__title field-title">PWA</span>
            <div class="setting" role="button" tabindex="0" data-action="pwa">
              <label><i class="uil uil-mobile-android" aria-hidden="true"></i> PWA Install</label>
            </div>
            <div class="setting" role="button" tabindex="0" data-action="shortcuts">
              <label><i class="uil uil-keyboard" aria-hidden="true"></i> Shortcuts</label>
            </div>
            <div class="setting" role="button" tabindex="0" data-action="fullscreen">
              <label><i class="uil uil-expand-arrows-alt" aria-hidden="true"></i> Fullscreen</label>
            </div>
            <span class="settings__title field-title">Appearence</span>
            <div class="setting">
              <input type="checkbox" id="dark_mode" checked disabled/>
              <label for="dark_mode"><i class="uil uil-moon-eclipse"></i> Dark Mode</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="animations" checked disabled/>
              <label for="animations"><i class="uil uil-minus-path"></i> Animations</label>
            </div>

            <span class="settings__title field-title">settings</span>
            <div class="setting">
              <input type="checkbox" id="uppercase" checked />
              <label for="uppercase"><i class="uil uil-letter-english-a"></i> Uppercase</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="lowercase" checked />
              <label for="lowercase"><i class="uil uil-font"></i> Lowercase</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="number" checked />
              <label for="number"><i class="uil uil-list-ol-alt"></i> Numbers</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="symbol" />
              <label for="symbol"><i class="uil uil-english-to-chinese"></i> Symbols</label>
            </div>

            <span class="settings__title field-title">cookies</span>
            <div class="setting">
              <input type="checkbox" id="cookies_functional" checked disabled/>
              <label for="cookies_functional"><i class="uil uil-puzzle-piece"></i> Functional</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="cookies_statistics" disabled/>
              <label for="cookies_statistics"><i class="uil uil-chart-pie-alt"></i> Statistics</label>
            </div>
            <div class="setting">
              <input type="checkbox" id="cookies_marketing" disabled/>
              <label for="cookies_marketing"><i class="uil uil-crosshairs"></i> Marketing</label>
            </div>

          </div>
          
          
        </div>
      </div>

    </div>

    <ul class="Navbar-quickLinks">
<?php foreach ($quickLinks as $link): ?>
      <li><a href="<?= $e($link['href']) ?>" aria-label="<?= $e(ucfirst($link['key'])) ?>"<?= $link['external'] ? ' target="_blank" rel="noopener"' : ' data-soon="' . $e(ucfirst($link['key'])) . '"' ?>><i class="uil uil-<?= $e($quickIcons[$link['key']]) ?> icon-3d<?= $link['key'] === 'telegram' ? ' u-tg-size' : '' ?>" aria-hidden="true"></i></a></li>
<?php endforeach; ?>
    </ul>
  </nav>

  