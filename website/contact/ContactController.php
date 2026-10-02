<?php

declare(strict_types=1);

namespace Website\Contact;

use Core\Helpers\View;
use Core\Logging\Logger;
use Core\Routing\HttpException;
use Core\Routing\Request;
use Core\Routing\Response;
use Core\Security\FormGuard;
use Core\Services\LeadStore;
use Core\Services\Notifier\Notifier;

/**
 * Contact / work-request flow:
 * request → FormGuard (size, CSRF, honeypot, form age, rate limit) → validate/sanitise →
 * server-side reference → persist → notify → PRG redirect.
 */
final class ContactController
{
    private const FLASH_KEY = '_contact_flash';

    public function __construct(
        private readonly View $view,
        private readonly FormGuard $guard,
        private readonly ContactValidator $validator,
        private readonly LeadStore $leads,
        private readonly Notifier $notifier,
        private readonly Logger $logger,
        private readonly string $mapboxToken,
        private readonly string $contactEmail,
    ) {}

    public function show(Request $request): Response
    {
        $tokens = $this->guard->tokens();

        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        return $this->page($tokens, [
            'reference' => is_array($flash) ? ($flash['reference'] ?? null) : null,
            'errors' => is_array($flash) ? ($flash['errors'] ?? []) : [],
            'old' => is_array($flash) ? ($flash['old'] ?? []) : [],
        ]);
    }

    public function submit(Request $request): Response
    {
        $guard = $this->guard->check($request, 'contact');

        if ($guard['outcome'] === FormGuard::HONEYPOT) {
            return $this->redirectSent('AEV-' . strtoupper(bin2hex(random_bytes(5))));
        }
        if ($guard['outcome'] === FormGuard::TOO_FAST) {
            return $this->fail(['form' => 'Please wait a moment and submit the form again.']);
        }

        $result = $this->validator->validate(
            $request->input('contact_email'),
            $request->input('contact_subject'),
            $request->input('contact_message'),
        );
        if ($result['errors'] !== []) {
            return $this->fail($result['errors'], $result['data']);
        }

        $data = $result['data'];
        $reference = LeadStore::newReference();

        $stored = $this->leads->append([
            'reference' => $reference,
            'created_at' => gmdate('c'),
            'source' => 'contact',
            'who' => $guard['who'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);
        if (!$stored) {
            $this->logger->error('contact.persist_failed', ['reference' => $reference]);
            throw new HttpException(500);
        }

        $delivered = $this->notifier->send($this->summary($reference, $data));
        $this->logger->info('contact.accepted', ['reference' => $reference, 'notified' => $delivered]);

        $this->guard->rotate();

        return $this->redirectSent($reference);
    }

    /**
     * @param array{csrf: string, ts: string} $tokens
     * @param array<string, mixed> $state
     */
    private function page(array $tokens, array $state): Response
    {
        $mapboxToken = $this->mapboxToken;
        $html = $this->view->render('contact::contact', $state + $tokens + ['mapboxToken' => $mapboxToken, 'email' => $this->contactEmail, 'navbar' => ['backHref' => '/', 'backIcon' => 'uil-estate']], [
            'title' => 'ΛΞV | Contact',
            'description' => 'Contact ΛΞV — tell us about your project.',
            'path' => '/contact',
            'bodyClass' => 'page-contact',
            'styles' => ['/assets/css/contact-fonts.css', '/assets/css/core.css', '/assets/vendor/mapbox-gl/mapbox-gl.css', '/assets/css/contact.css'],
            'scripts' => $mapboxToken === '' ? ['/assets/js/contact.js'] : ['/assets/vendor/mapbox-gl/mapbox-gl.js', '/assets/js/contact.js'],
        ], 'page');
        $response = (new Response($html))->withHeader('Cache-Control', 'no-store');

        // Mapbox GL needs its tile/event hosts, blob workers and data/blob images — only on this page, only with a token.
        return $mapboxToken === '' ? $response : $response->withCsp([
            'connect-src' => "'self' https://api.mapbox.com https://events.mapbox.com",
            'img-src' => "'self' data: blob: https://api.mapbox.com",
            'worker-src' => "'self' blob:",
            'child-src' => 'blob:',
        ]);
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function fail(array $errors, array $old = []): Response
    {
        $_SESSION[self::FLASH_KEY] = ['errors' => $errors, 'old' => array_intersect_key($old, array_flip(['email', 'subject', 'message']))];

        return Response::redirect('/contact');
    }

    private function redirectSent(string $reference): Response
    {
        $_SESSION[self::FLASH_KEY] = ['reference' => $reference];

        return Response::redirect('/contact');
    }

    /** @param array{email: string, subject: string, message: string} $data */
    private function summary(string $reference, array $data): string
    {
        return "[ New Contact Request {$reference} ]\n\n"
            . 'Email: ' . $data['email'] . "\n"
            . 'Subject: ' . $data['subject'] . "\n\n"
            . 'Message: ' . $data['message'];
    }
}
