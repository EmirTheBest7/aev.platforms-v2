<?php

declare(strict_types=1);

/**
 * What the Downloads page offers. Paths are URLs under public/ (a feature test checks that every `file` exists).
 * An entry without a `file` is shown as "not available yet" — nothing is invented.
 *
 * logos:      name · preview (image shown on the card) · file (download) · invert (preview is dark-on-transparent)
 * wallpapers: name · action {label, href}
 * docs:       name · file|null
 */
return [
    'logos' => [
        ['name' => 'Logo_', 'preview' => '/build/images/brand/ALIEV.svg', 'file' => '/build/images/brand/ALIEV.svg', 'invert' => false, 'guide' => '/home/_api/Docs/'],
        ['name' => 'E.COM', 'preview' => '/build/images/brand/weblogo.svg', 'file' => '/build/images/brand/weblogo.svg', 'invert' => true],
        ['name' => 'Dreamers', 'preview' => '/build/images/brand/Dreamers.svg', 'file' => '/build/images/brand/Dreamers.svg', 'invert' => true],
        ['name' => 'Logo_3D', 'preview' => '/build/images/brand/ALIEV_3D.png', 'file' => '/build/images/brand/ALIEV_3D.png', 'invert' => false],
    ],
    'wallpapers' => [
        ['name' => 'Unique', 'preview' => '/build/images/brand/ALIEV.svg', 'action' => ['label' => 'Create', 'href' => '/downloads/wallpapers/create/']],
    ],
    'docs' => [
        ['name' => 'Whitepaper.pdf', 'file' => null],
        ['name' => 'AAEV_Keynote.pptx', 'file' => null],
        ['name' => 'ACS_System.pdf', 'file' => '/downloads/docs/ACS_System.pdf'],
    ],
];
