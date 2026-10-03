<?php

declare(strict_types=1);

namespace Website\Home;

use Core\Helpers\Config;
use Core\Helpers\View;
use Core\Routing\Request;
use Core\Routing\Response;
use Core\Security\FormGuard;

/**
 * The ecosystem entry point (legacy page/main). Builds the view model for the shell
 * (launcher, profile panel, settings, integrations) and the five sections.
 */
final class HomeController
{
    public function __construct(
        private readonly View $view,
        private readonly Config $config,
        private readonly AppCatalog $apps,
        private readonly FormGuard $guard,
        /** @var \Closure(): ?\Core\Auth\DTO\AuthenticatedUser */
        private readonly \Closure $currentUser,
    ) {}

    public function home(Request $request): Response
    {
        $tokens = $this->guard->tokens();

        $flash = $_SESSION[HireController::FLASH_KEY] ?? null;
        unset($_SESSION[HireController::FLASH_KEY]);

        $authEnabled = $this->config->bool('integrations.auth_enabled');
        $user = ($this->currentUser)();
        $launcher = $this->apps->launcher();

        $directory = array_map(
            static fn(array $a): array => ['name' => $a['name'], 'href' => $a['href'], 'external' => $a['external'], 'soon' => $a['soon'], 'action' => $a['action']],
            $this->apps->directory(),
        );

        $chatId = $this->config->string('integrations.intergram.chat_id');
        $intergram = $chatId === '' ? null : [
            'id' => $chatId,
            'server' => $this->config->string('integrations.intergram.server'),
            'titleOpen' => $this->config->string('integrations.intergram.title_open'),
            'intro' => $this->config->string('integrations.intergram.intro'),
            'mainColor' => $this->config->string('integrations.intergram.main_color'),
        ];

        $html = $this->view->render('home::home', [
            'user' => $user === null
                ? ['authenticated' => false, 'name' => 'Hi, User!', 'email' => $this->config->string('integrations.destinations.email'), 'avatar' => '/build/images/avatar.png']
                : ['authenticated' => true, 'name' => 'Hi, ' . $user->username . '!', 'email' => $user->email, 'avatar' => '/build/images/avatar.png'],
            'authEnabled' => $authEnabled,
            'signInHref' => $authEnabled ? '/home/auth' : '/',
            'apps' => $launcher,
            'directoryJson' => $this->json($directory),
            'quickLinks' => $this->apps->destinations(['instagram', 'youtube', 'telegram']),
            'mobileLinks' => $this->apps->destinations(['facebook', 'twitter', 'instagram']),
            'email' => $this->config->string('integrations.destinations.email'),
            'telegram' => $this->config->string('integrations.destinations.telegram'),
            'instagram' => $this->config->string('integrations.destinations.instagram'),
            'services' => HireValidator::SERVICES,
            'ticker' => $this->ticker(),
            'works' => $this->works(),
            'telegramNews' => $this->config->string('integrations.destinations.telegram_news'),
            'docsHref' => $this->config->string('integrations.destinations.docs'),
            'docsSoon' => $this->config->string('integrations.destinations.docs') === '',
            'intergramJson' => $intergram === null ? '' : $this->json($intergram),
            'hireFlash' => is_array($flash) ? $flash : null,
            'weekday' => date('l'),
            'dayMonth' => date('j') . '. ' . date('F'),
            'csrf' => $tokens['csrf'],
            'ts' => $tokens['ts'],
        ], [
            'title' => 'ΛΞV | Digital studio.',
            'description' => 'We design and develop beautiful and effective digital products. Our smart innovations will make you shine online. Based in Prague.',
            'path' => '/',
            'bodyClass' => 'page-home',
        ], 'shell');

        $response = (new Response($html))->withHeader('Cache-Control', 'no-store');

        // The only third-party origin on the page: Intergram's chat iframe (its widget script is self-hosted).
        return $intergram === null ? $response : $response->withCsp(['frame-src' => "'self' " . $intergram['server']]);
    }

    /**
     * Cards of the "Selected work" slider (config/works.php).
     *
     * @return list<array{name: string, title: string, image: string, description: string, position: string|null}>
     */
    private function works(): array
    {
        $works = [];
        foreach ((array) $this->config->get('works.items', []) as $item) {
            $position = $item['position'] ?? null;
            $works[] = [
                'name' => (string) ($item['name'] ?? ''),
                'title' => (string) ($item['title'] ?? $item['name'] ?? ''),
                'image' => (string) ($item['image'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
                'position' => is_string($position) && $position !== '' ? $position : null,
            ];
        }

        return $works;
    }

    /**
     * Legacy marquee order and logos; prices are filled in by the page script from /api/prices.
     *
     * @return list<array{symbol: string, icon: string}>
     */
    private function ticker(): array
    {
        $icons = ['BTC' => 'btc.png', 'ETH' => 'eth.png', 'AEVT' => 'aevt.png', 'AEVD' => 'aevd.png', 'USDT' => 'usdt.png', 'SOL' => 'sol.png', 'TON' => 'ton.png', 'DOT' => 'dot.gif', 'SUI' => 'sui.png', 'APT' => 'apt.png'];
        $out = [];
        foreach (array_keys((array) $this->config->get('market.symbols', [])) as $symbol) {
            if (isset($icons[$symbol])) {
                $out[] = ['symbol' => (string) $symbol, 'icon' => $icons[$symbol]];
            }
        }

        return $out;
    }

    /** @param array<mixed> $data */
    private function json(array $data): string
    {
        // Safe inside <script type="application/json">: neutralise "</script", "<!--" and U+2028/2029.
        return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
}
