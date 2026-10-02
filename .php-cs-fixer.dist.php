<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/core', __DIR__ . '/website', __DIR__ . '/api', __DIR__ . '/config', __DIR__ . '/routes', __DIR__ . '/tests', __DIR__ . '/public'])
    ->name('*.php')
    ->notPath('#^(public/build|public/home)/#');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
    ])
    ->setFinder($finder);
