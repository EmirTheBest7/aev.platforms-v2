<?php
/**
 * Downloads (port of the original page/downloads). Content comes from config/downloads.php.
 *
 * @var callable(mixed): string $e
 * @var list<array<string, mixed>> $logos
 * @var list<array<string, mixed>> $wallpapers
 * @var list<array<string, mixed>> $docs
 */
?>
    <main id="main" class="downloads">
        <div class="main-content">

            <!-- Header -->
            <div class="header">
                <div class="header__container">
                    <div class="header__left">
                        <h1>Downloads</h1>
                        <p>Here, you can find important Λ L I Ξ V documents, which can be beneficial for cooperation.</p>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <nav class="c-tabs secondary-nav" data-toggle="c-tabs" aria-label="Downloads">
                <ul class="c-tab--navigation secondary-nav__list">
                    <li class="c-tab--item secondary-nav__item active"><a href="#logos" class="active">Logos</a></li>
                    <li class="c-tab--item secondary-nav__item"><a href="#wallpapers">Wallpapers</a></li>
                    <li class="c-tab--item secondary-nav__item"><a href="#docs">Docs</a></li>
                    <li class="c-tab--slider">
                        <div class="c-tab-indicator"></div>
                    </li>
                </ul>
            </nav>
            <div class="c-tab--content-container">
                <div id="logos" class="c-tab--content active">
                    <article class="video-sec-wrap">
                        <div class="video-sec">
                            <ul class="video-sec-middle">
<?php foreach ($logos as $logo): ?>
                                <li class="thumb-wrap">
                                    <div class="card">
                                        <div class="front">
                                            <div class="branded">
                                                <img<?= $logo['invert'] ? ' class="invert"' : '' ?> src="<?= $e($logo['preview']) ?>" alt="<?= $e($logo['name']) ?>">
                                            </div>
                                            <div class="content">
                                                <div class="main">
                                                    <div><?= $e($logo['name']) ?></div>
<?php if (isset($logo['guide'])): ?>
                                                    <a href="<?= $e($logo['guide']) ?>" class="btn ripple-button btn--icon" target="_blank" rel="noopener" aria-label="Brand guide"><i class="bi bi-filetype-doc" aria-hidden="true"></i></a>
                                                    <a href="<?= $e($logo['file']) ?>" class="btn ripple-button btn--icon" download aria-label="Download <?= $e($logo['name']) ?>"><i class="bi bi-cloud-download" aria-hidden="true"></i></a>
<?php else: ?>
                                                    <a href="<?= $e($logo['file']) ?>" class="btn ripple-button" download>Download</a>
<?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                </div>
                <div id="wallpapers" class="c-tab--content">
                    <article class="video-sec-wrap">
                        <div class="video-sec">
                            <ul class="video-sec-middle">
<?php foreach ($wallpapers as $wallpaper): ?>
                                <li class="thumb-wrap">
                                    <div class="card">
                                        <div class="front">
                                            <div class="branded">
                                                <img src="<?= $e($wallpaper['preview']) ?>" alt="<?= $e($wallpaper['name']) ?>">
                                            </div>
                                            <div class="content">
                                                <div class="main">
                                                    <div><?= $e($wallpaper['name']) ?></div>
                                                    <a href="<?= $e($wallpaper['action']['href']) ?>" class="btn ripple-button" target="_blank" rel="noopener"><?= $e($wallpaper['action']['label']) ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
<?php endforeach; ?>
                            </ul>
                        </div>
                    </article>
                </div>
                <div id="docs" class="c-tab--content">
                    <table class="rwd-table video-sec">
                        <tbody>
                            <tr>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
<?php foreach ($docs as $doc): ?>
                            <tr>
                                <td data-th="File Name"><?= $e($doc['name']) ?></td>
                                <td data-th="Action">
<?php if ($doc['file'] !== null): ?>
                                    <a class="ripple-button" href="<?= $e($doc['file']) ?>" download>Download</a>
<?php else: ?>
                                    <span class="not-available">Not available yet</span>
<?php endif; ?>
                                </td>
                            </tr>
<?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
