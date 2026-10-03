<?php

declare(strict_types=1);

namespace Website\Legal;

use Core\Helpers\Config;
use Core\Helpers\View;
use Core\Routing\Request;
use Core\Routing\Response;

/**
 * Privacy Policy and Imprint. Content comes from config/legal.php (operator details from LEGAL_* environment variables).
 * While anything required is missing, the page says so openly and stays out of search engines — nothing is invented.
 */
final class LegalController
{
    public function __construct(
        private readonly View $view,
        private readonly Config $config,
    ) {}

    public function privacy(Request $request): Response
    {
        $sections = [];
        $complete = true;
        foreach ((array) $this->config->get('legal.privacy.sections', []) as $section) {
            $body = array_values(array_filter((array) ($section['body'] ?? []), 'is_string'));
            $complete = $complete && $body !== [];
            $sections[] = ['heading' => (string) ($section['heading'] ?? ''), 'body' => $body];
        }
        [, $operatorComplete] = $this->operator();

        return $this->page('privacy', 'Privacy Policy', 'How ΛΞV handles personal data.', [
            'sections' => $sections,
            'updated' => $this->config->string('legal.privacy.updated', ''),
        ], $complete && $operatorComplete);
    }

    public function imprint(Request $request): Response
    {
        [$operator, $complete] = $this->operator();

        return $this->page('imprint', 'Imprint', 'Who operates ΛΞV.', [
            'operator' => $operator,
            'email' => $this->config->string('integrations.destinations.email', ''),
        ], $complete);
    }

    /** @return array{0: list<array{label: string, value: string, required: bool}>, 1: bool} fields to show, all required present */
    private function operator(): array
    {
        $fields = [];
        $complete = true;
        foreach ((array) $this->config->get('legal.operator', []) as $field) {
            $value = trim((string) ($field['value'] ?? ''));
            $required = (bool) ($field['required'] ?? false);
            $complete = $complete && !($required && $value === '');
            if ($value !== '' || $required) {
                $fields[] = ['label' => (string) ($field['label'] ?? ''), 'value' => $value, 'required' => $required];
            }
        }

        return [$fields, $complete];
    }

    /** @param array<string, mixed> $data */
    private function page(string $name, string $title, string $description, array $data, bool $complete): Response
    {
        $html = $this->view->render('legal::' . $name, $data + [
            'title' => $title,
            'complete' => $complete,
            'navbar' => ['leading' => 'link', 'href' => '/', 'icon' => 'uil-estate', 'label' => 'Home'],
        ], [
            'title' => 'ΛΞV | ' . $title,
            'description' => $description,
            'path' => '/' . $name,
            'noindex' => !$complete,
            'bodyClass' => 'page-legal',
            'styles' => ['/build/css/fonts/open-sans.css', '/build/css/core.css', '/build/legal/css/legal.css'],
        ], 'page');

        return new Response($html);
    }
}
