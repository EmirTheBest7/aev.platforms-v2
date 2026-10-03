<?php

declare(strict_types=1);

/**
 * "Selected work" slider on the main page (after the fixed "ΛΞV Community" card).
 *
 * name · title (heading shown on the card) · image (URL under public/, checked by a test) · description
 * position: slider layout class suffix — 'right' (first card after Community), 'left' (last card), null (middle cards)
 * confirmed: false = carried over from the original site and NOT yet confirmed by the owner as agency work.
 *            It has no effect on rendering; it marks what must be reviewed before launch. Replace the entry (or
 *            delete it) once the owner has decided; keep the first card 'right' and the last card 'left'.
 *
 * OWNER CONFIRMATION REQUIRED for every entry below — images are placeholders from the original theme
 * (the same stock photo is used for three cards) and the descriptions are the original taglines.
 */
return [
    'items' => [
        ['name' => 'Dreamers', 'title' => 'Dreamers', 'image' => '/build/home/images/home/work-alex-nowak.jpg', 'description' => 'Next station? Web3.0!', 'position' => 'right', 'confirmed' => false],
        ['name' => 'Cerebro Blockchain', 'title' => 'Cerebro Blockchain', 'image' => '/build/home/images/home/work-alex-nowak.jpg', 'description' => 'Blockchain powered dApps', 'position' => null, 'confirmed' => false],
        ['name' => 'Cortex Browser', 'title' => 'Cortex Browser', 'image' => '/build/home/images/home/work-alex-nowak.jpg', 'description' => "We're on a mission man, internet free state.", 'position' => null, 'confirmed' => false],
        ['name' => 'EROS', 'title' => 'EROS 💻', 'image' => '/build/home/images/home/IMG_7781.jpg', 'description' => 'Family of operating systems that use the EROS kernel and are open source', 'position' => 'left', 'confirmed' => false],
    ],
];
