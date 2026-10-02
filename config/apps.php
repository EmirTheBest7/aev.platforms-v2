<?php

declare(strict_types=1);

/**
 * The ecosystem's app switcher — single source for the visible launcher grid AND the
 * Spotlight "All Apps" list. NOTHING IS REMOVED from the legacy lists (docs/APPLICATIONS.md):
 * entries whose product is not rebuilt yet keep their tile and show the designed pending
 * notification instead of linking nowhere.
 *
 * Keys: name · icon (file in /build/home/images/home/icons, or "avatar") · href (internal path or URL)
 *       · needs (config key in integrations.destinations, or "auth") · action (JS hook)
 *       · status ("pending" = product not rebuilt yet) · round (avatar styling)
 */
return [
    'launcher' => [
        ['name' => 'Account', 'icon' => 'avatar', 'round' => true, 'needs' => 'auth', 'href' => '/home/auth/'],
        ['name' => '_API', 'icon' => 'Cloudshot.svg', 'href' => '/home/_api/UI/'],
        ['name' => 'Docs', 'icon' => 'Book.svg', 'href' => '/home/_api/Docs/'],
        ['name' => 'Contacts', 'icon' => 'Paste.svg', 'href' => '/contact'],
        ['name' => 'Jobs', 'icon' => 'Folder.svg', 'href' => '/careers'],
        ['name' => 'Downloads', 'icon' => 'Collectibles.svg', 'href' => '/downloads'],
    ],

    // Spotlight search directory: retained pages and public destinations only.
    'directory' => [
        ['name' => 'ΛΞV Account', 'needs' => 'auth', 'href' => '/home/auth/'],
        ['name' => 'ΛΞV _API', 'href' => '/home/_api/UI/'],
        ['name' => 'ΛΞV Docs', 'href' => '/home/_api/Docs/'],
        ['name' => 'ΛΞV Contacts', 'href' => '/contact'],
        ['name' => 'ΛΞV Jobs', 'href' => '/careers'],
        ['name' => 'ΛΞV Downloads', 'href' => '/downloads'],
        ['name' => 'Instagram', 'needs' => 'instagram'],
        ['name' => 'Telegram', 'needs' => 'telegram_news'],
        ['name' => 'Home Page', 'href' => '/'],
    ],
];
