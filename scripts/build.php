<?php

declare(strict_types=1);

/**
 * Publishes the front-end sources into the web root.
 *
 *   php scripts/build.php            incremental (size + mtime), removes files that no longer have a source
 *
 * Sources                         → published at
 *   resources/{css,js,images,icons,fonts,vendor}  → public/build/<same name>/
 *   website/<page>/assets/         → public/build/<page>/
 *   core/auth/web/assets/          → public/build/auth/
 *   api/terminal/bundle/           → public/home/_api/        (the retained static bundle; URL compatibility)
 *
 * Only web file types are published (an allow-list): fonts that may not be web-served (resources/fonts/*.ttf — SF Pro,
 * DotlineBold), archives and sources never reach public/. Modification times are preserved so the cache-busting
 * ?v=<mtime> of View::asset() only changes when a file really changes. public/build and public/home are generated
 * output (git-ignored); downloads stay in public/downloads.
 */

$root = dirname(__DIR__);

// Allow-list of web file types for shared and page assets; the _api bundle ships whole (it contains its own downloadable archive).
$web = ['css', 'js', 'woff2', 'woff', 'otf', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico', 'json', 'txt'];

$targets = [];
foreach (['css', 'js', 'images', 'icons', 'fonts', 'vendor'] as $dir) {
    $targets[] = ["$root/resources/$dir", "$root/public/build/$dir", $web];
}
foreach (['home', 'careers', 'contact', 'downloads', 'legal'] as $page) {
    $targets[] = ["$root/website/$page/assets", "$root/public/build/$page", $web];
}
$targets[] = ["$root/core/auth/web/assets", "$root/public/build/auth", $web];
$targets[] = ["$root/api/terminal/bundle", "$root/public/home/_api", null];

$copied = $removed = $kept = 0;
foreach ($targets as [$source, $destination, $allowed]) {
    $wanted = [];
    if (is_dir($source)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() === '.DS_Store') {
                continue;
            }
            $extension = strtolower($file->getExtension());
            if ($allowed !== null && !in_array($extension, $allowed, true)) {
                continue;
            }
            $relative = substr($file->getPathname(), strlen($source) + 1);
            $wanted[$relative] = true;
            $target = $destination . '/' . $relative;
            if (is_file($target) && filesize($target) === $file->getSize() && filemtime($target) === $file->getMTime()) {
                ++$kept;
                continue;
            }
            if (!is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }
            copy($file->getPathname(), $target);
            touch($target, $file->getMTime());
            ++$copied;
        }
    }
    // prune generated files whose source is gone, then empty directories
    if (is_dir($destination)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($destination, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $entry) {
            $relative = substr($entry->getPathname(), strlen($destination) + 1);
            if ($entry->isFile() && !isset($wanted[$relative])) {
                unlink($entry->getPathname());
                ++$removed;
            } elseif ($entry->isDir() && count(scandir($entry->getPathname()) ?: []) <= 2) {
                rmdir($entry->getPathname());
            }
        }
    }
}
echo "build: {$copied} published, {$kept} unchanged, {$removed} removed\n";
