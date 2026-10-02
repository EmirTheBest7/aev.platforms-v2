<?php

declare(strict_types=1);

namespace Website\Home;

use Core\Helpers\Config;

/**
 * Resolves config/apps.php against what actually exists right now (enabled features, configured
 * destinations). The result is what both the launcher grid and Spotlight render, so a tile can
 * never point at a destination that the application knows is missing.
 */
final class AppCatalog
{
    private const ICON_BASE = '/build/home/images/home/icons/';

    public function __construct(private readonly Config $config) {}

    /**
     * @return list<array{name: string, icon: string, href: string, external: bool, action: string, soon: bool, round: bool}>
     */
    public function launcher(): array
    {
        $out = [];
        foreach ($this->entries('apps.launcher') as $entry) {
            $resolved = $this->resolve($entry);
            $icon = (string) ($entry['icon'] ?? '');
            $out[] = $resolved + [
                'icon' => $icon === 'avatar' ? 'avatar' : self::ICON_BASE . rawurlencode($icon),
                'round' => (bool) ($entry['round'] ?? false),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{name: string, href: string, external: bool, action: string, soon: bool}>
     */
    public function directory(): array
    {
        return array_map(fn(array $entry): array => $this->resolve($entry), $this->entries('apps.directory'));
    }

    /**
     * Social/quick links in a given order. Unconfigured ones stay in the UI as pending controls.
     *
     * @param list<string> $keys keys of integrations.destinations
     * @return list<array{key: string, href: string, external: bool, soon: bool}>
     */
    public function destinations(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $url = $this->config->string('integrations.destinations.' . $key);
            $out[] = ['key' => $key, 'href' => $url !== '' ? $url : '/', 'external' => $url !== '', 'soon' => $url === ''];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array{name: string, href: string, external: bool, action: string, soon: bool}
     */
    private function resolve(array $entry): array
    {
        $href = (string) ($entry['href'] ?? '');
        $soon = ($entry['status'] ?? '') === 'pending';
        $needs = (string) ($entry['needs'] ?? '');

        if ($needs === 'auth') {
            $soon = !$this->config->bool('integrations.auth_enabled');
        } elseif ($needs !== '') {
            $href = $this->config->string('integrations.destinations.' . $needs);
            $soon = $href === '';
        }

        if ($soon || $href === '') {
            $href = '/';
            $soon = true;
        }

        return [
            'name' => (string) $entry['name'],
            'href' => $href,
            'external' => str_starts_with($href, 'https://'),
            'action' => $soon ? '' : (string) ($entry['action'] ?? ''),
            'soon' => $soon,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function entries(string $key): array
    {
        $value = $this->config->get($key, []);

        return is_array($value) ? array_values(array_filter($value, is_array(...))) : [];
    }
}
