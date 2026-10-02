<?php
/**
 * The five sections of the ecosystem entry point (legacy page/main), unchanged in structure.
 *
 * @var callable(mixed): string $e
 * @var \Core\Helpers\View $view
 * @var list<array{symbol: string, icon: string}> $ticker
 * @var string $telegram
 * @var string $telegramNews
 * @var string $instagram
 * @var string $email
 * @var string $csrf
 * @var string $ts
 * @var array<string, mixed>|null $hireFlash
 */
?>
<div id="viewport" class="l-viewport">
      <div class="l-wrapper">

        <nav class="l-side-nav" aria-label="Sections">
          <ul class="side-nav">
            <li class="is-active" tabindex="0" role="button" data-section="home"><span>Home</span></li>
            <li tabindex="0" role="button" data-section="works"><span>Works</span></li>
            <li tabindex="0" role="button" data-section="about"><span>About</span></li>
            <li tabindex="0" role="button" data-section="contact"><span>Contact</span></li>
            <li tabindex="0" role="button" data-section="hire"><span>Hire us</span></li>
          </ul>
        </nav>
        <ul class="l-main-content main-content">
          <li id="home" class="l-section section section--is-active">
            <div class="intro">
              <div class="intro--banner">
                <div class="marquee">
                  <ul class="marquee-content" aria-label="Market prices">
<?php foreach ($ticker as $coin): ?>
                    <li><img src="/build/home/images/crypto/<?= $e($coin['icon']) ?>" alt="<?= $e($coin['symbol']) ?>"> <?= $e($coin['symbol']) ?> $<span data-price="<?= $e($coin['symbol']) ?>">—</span></li>
<?php endforeach; ?>
                  </ul>
                </div>
                <!--<h1>Your next<br>interactive<br>experience</h1>-->
                <!--<h1>Shape the future<br>with us at<br><span id="dc25">ΛWDC2025</span></h1>-->
                <h1>Build your<br>digital success<br>with us<span class="u-5ce70ec">.</span></h1>
                <button type="button" class="cta" data-section="hire">Hire Us
                  <svg version="1.1" id="Layer_1" 
                     x="0px" y="0px" viewBox="0 0 150 118"
                    xml:space="preserve">
                    <g transform="translate(0.000000,118.000000) scale(0.100000,-0.100000)">
                      <path
                        d="M870,1167c-34-17-55-57-46-90c3-15,81-100,194-211l187-185l-565-1c-431,0-571-3-590-13c-55-28-64-94-18-137c21-20,33-20,597-20h575l-192-193C800,103,794,94,849,39c20-20,39-29,61-29c28,0,63,30,298,262c147,144,272,271,279,282c30,51,23,60-219,304C947,1180,926,1196,870,1167z" />
                    </g>
                  </svg>
                  <span class="btn-background"></span>
                </button>
                <div id="globeCanvas"></div>
              </div>
              <div class="intro--options">
              <a class="u-f66e76d" href="/home/_api/UI/terminal/Page/4ukraine/">
                <h3 class="u-6ea5894">#StopTheWar <img class="u-fa8cf5d" alt="Ukraine" src="/build/home/images/home/ukr_flag.svg"></h3>
                <p class="u-8df98a9">Help Ukraine win this war by donating to local charities.</p>
                <span class="ripple-button u-62ea1e5">
                  Donate
                </span>
              </a>
              <a class="u-4dafad1" href="/careers">
                  <h3 class="u-6ea5894">We Are Hiring <i class="uil uil-cube"></i></h3>
                  <p class="u-8df98a9">New roles available in our team. Let's make something great</p>

                  <span class="ripple-button u-62ea1e5">
                  Join Us
                </span>
              </a>
              </div>
            </div>
          </li>
          <li id="works" class="l-section section">
            <div class="work">
              <h2>Selected work</h2>
              <div class="work--lockup">
                <ul class="slider">
                  <li class="slider--item slider--item-center">
                    <a target="_blank" rel="noopener" href="<?= $e($telegramNews) ?>">
                      <div class="slider--item-image">
                        <img src="/build/home/images/home/IMG_2285.JPG" alt="ΛΞV Community">
                      </div>
                      <p class="slider--item-title">ΛΞV Community.</p>
                      <p class="slider--item-description">
                        Connecting inovators around the world.<br/> Feel free to join us & stay tunned!
                      </p>
                    </a>
                  </li>
                  <li class="slider--item slider--item-right">
                    <a>
                      <div class="slider--item-image">
                        <img src="/build/home/images/home/work-alex-nowak.jpg" alt="Dreamers">
                      </div>
                      <p class="slider--item-title">Dreamers</p>
                      <p class="slider--item-description">
                        Next station? Web3.0!
                      </p>
                    </a>
                  </li>
                  <li class="slider--item">
                    <a>
                      <div class="slider--item-image">
                        <img src="/build/home/images/home/work-alex-nowak.jpg" alt="Cerebro Blockchain">
                      </div>
                      <p class="slider--item-title">Cerebro Blockchain</p>
                      <p class="slider--item-description">
                        Blockchain powered dApps
                      </p>
                    </a>
                  </li>
                  <li class="slider--item">
                    <a>
                      <div class="slider--item-image">
                        <img src="/build/home/images/home/work-alex-nowak.jpg" alt="Cortex Browser">
                      </div>
                      <p class="slider--item-title">Cortex Browser</p>
                      <p class="slider--item-description">
                        We're on a mission man, internet free state.
                      </p>
                    </a>
                  </li>
                  <li class="slider--item slider--item-left">
                    <a>
                      <div class="slider--item-image">
                        <img src="/build/home/images/home/IMG_7781.jpg" alt="EROS">
                      </div>
                      <p class="slider--item-title">EROS 💻</p>
                      <p class="slider--item-description">
                        Family of operating systems that use the EROS kernel and are open source
                      </p>
                    </a>
                  </li>
                </ul>
                <div class="slider--prev" role="button" tabindex="0" aria-label="Previous project">
                  <svg version="1.1" id="Layer_1" 
                     x="0px" y="0px" viewBox="0 0 150 118"
                    xml:space="preserve">
                    <g transform="translate(0.000000,118.000000) scale(0.100000,-0.100000)">
                      <path d="M561,1169C525,1155,10,640,3,612c-3-13,1-36,8-52c8-15,134-145,281-289C527,41,562,10,590,10c22,0,41,9,61,29
                        c55,55,49,64-163,278L296,510h575c564,0,576,0,597,20c46,43,37,109-18,137c-19,10-159,13-590,13l-565,1l182,180
                        c101,99,187,188,193,199c16,30,12,57-12,84C631,1174,595,1183,561,1169z" />
                    </g>
                  </svg>
                </div>
                <div class="slider--next" role="button" tabindex="0" aria-label="Next project">
                  <svg version="1.1" id="Layer_1" 
                     x="0px" y="0px" viewBox="0 0 150 118"
                    xml:space="preserve">
                    <g transform="translate(0.000000,118.000000) scale(0.100000,-0.100000)">
                      <path
                        d="M870,1167c-34-17-55-57-46-90c3-15,81-100,194-211l187-185l-565-1c-431,0-571-3-590-13c-55-28-64-94-18-137c21-20,33-20,597-20h575l-192-193C800,103,794,94,849,39c20-20,39-29,61-29c28,0,63,30,298,262c147,144,272,271,279,282c30,51,23,60-219,304C947,1180,926,1196,870,1167z" />
                    </g>
                  </svg>
                </div>
              </div>
            </div>
          </li>
          <li id="about" class="l-section section">
            <div class="about">
              <div class="about--banner">
                <h2>We<br>believe in<br>passionate<br>people</h2>
                <a href="/careers">Career
                  <span>
                    <svg version="1.1" id="Layer_1" 
                       x="0px" y="0px" viewBox="0 0 150 118"
                      xml:space="preserve">
                      <g transform="translate(0.000000,118.000000) scale(0.100000,-0.100000)">
                        <path
                          d="M870,1167c-34-17-55-57-46-90c3-15,81-100,194-211l187-185l-565-1c-431,0-571-3-590-13c-55-28-64-94-18-137c21-20,33-20,597-20h575l-192-193C800,103,794,94,849,39c20-20,39-29,61-29c28,0,63,30,298,262c147,144,272,271,279,282c30,51,23,60-219,304C947,1180,926,1196,870,1167z" />
                      </g>
                    </svg>
                  </span>
                </a>
                <!--<img src="/build/home/images/home/about-visual.png" alt="About Us">-->
                <?= $view->partial('home::partials/room') ?>
              </div>
              <div class="about--options">
                <a href="/careers/team">
                  <h3>Our Team</h3>
                </a>
                <a>
                  <h3>Philosophy</h3>
                </a>
                <a>
                  <h3>History</h3>
                </a>
              </div>
            </div>
          </li>
          <li id="contact" class="l-section section">
            <div class="contact">
              <div class="contact--lockup">
                <div class="modal">
                  <div class="modal--information">
                    <p>Prague / Karlovy Vary, Czech Republic 🇨🇿</p>
                    <a href="mailto:<?= $e($email) ?>"><?= $e($email) ?></a>
                  </div>
                  <ul class="modal--options">
                    <li>
                      <a class="u-b456ef3" href="<?= $e($telegram) ?>" target="_blank" rel="noopener" aria-label="Telegram"><i class="uil uil-telegram-alt" aria-hidden="true"></i></a>
                      <a class="u-05185b8" href="<?= $e($instagram) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                        <i class="uil uil-instagram" aria-hidden="true"></i>
                      </a>
                    </li>
                    <li><a href="/downloads"><i class="uil uil-cube" aria-hidden="true"></i>&nbsp;&nbsp;Λ L I Ξ V</a></li>
                    <li><a href="mailto:<?= $e($email) ?>">Contact Us</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </li>
          <li id="hire" class="l-section section">
            <div class="hire">
              <h2>You want us to do</h2>
              <form class="work-request" action="/hire" method="post" data-hire-form>
                <?= $view->partial('components/guard-fields', ['csrf' => $csrf, 'ts' => $ts]) ?>
                <div class="work-request--options">
                  <span class="options-a">
                    <input id="opt-1" name="services[]" type="checkbox" value="app design">
                    <label for="opt-1">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      App Design
                    </label>
                    <input id="opt-2" name="services[]" type="checkbox" value="graphic design">
                    <label for="opt-2">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      Graphic Design
                    </label>
                    <input id="opt-3" name="services[]" type="checkbox" value="motion design">
                    <label for="opt-3">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      Motion Design
                    </label>
                  </span>
                  <span class="options-b">
                    <input id="opt-4" name="services[]" type="checkbox" value="ux design">
                    <label for="opt-4">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      UX Design
                    </label>
                    <input id="opt-5" name="services[]" type="checkbox" value="webdesign">
                    <label for="opt-5">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      Webdesign
                    </label>
                    <input id="opt-6" name="services[]" type="checkbox" value="marketing">
                    <label for="opt-6">
                      <svg version="1.1" id="Layer_1" 
                         x="0px" y="0px" viewBox="0 0 150 111"
                        xml:space="preserve">
                        <g transform="translate(0.000000,111.000000) scale(0.100000,-0.100000)">
                          <path
                            d="M950,705L555,310L360,505C253,612,160,700,155,700c-6,0-44-34-85-75l-75-75l278-278L550-5l475,475c261,261,475,480,475,485c0,13-132,145-145,145C1349,1100,1167,922,950,705z" />
                        </g>
                      </svg>
                      Marketing
                    </label>
                  </span>
                </div>
                <div class="work-request--information">
                  <div class="information-name">
                    <input id="name" name="name" type="text" spellcheck="false" autocomplete="name" required minlength="2" maxlength="80">
                    <label for="name">Name</label>
                  </div>
                  <div class="information-email">
                    <input id="email" name="email" type="email" spellcheck="false" autocomplete="email" required maxlength="254">
                    <label for="email">Email</label>
                  </div>
                </div>
                <input type="submit" value="Send Request">
                <p class="hire-message" role="status" data-hire-message><?php if ($hireFlash !== null): ?><?= $hireFlash['ok'] ? 'Request sent. Reference ' . $e($hireFlash['reference']) : $e(implode(' ', (array) $hireFlash['errors'])) ?><?php endif; ?></p>
              </form>
            </div>
          </li>
        </ul>
      </div>
    </div>

  