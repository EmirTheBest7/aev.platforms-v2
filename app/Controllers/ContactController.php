<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Security\FormGuard;
use App\Services\LeadStore;
use App\Services\Notifier\Notifier;
use App\Support\Logger;
use App\Support\View;
use App\Validation\ContactValidator;

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
            $request->input('name'),
            $request->input('email'),
            $request->input('company'),
            $request->inputList('services'),
            $request->input('message'),
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
            'name' => $data['name'],
            'email' => $data['email'],
            'company' => $data['company'],
            'services' => $data['services'],
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
        $html = $this->view->render('pages/contact', $state + $tokens + ['services' => ContactValidator::SERVICES], [
            'title' => 'Contact | ΛΞV',
            'description' => 'Tell us about your project — ALIEV.IO is a digital studio building websites, platforms and automation.',
            'path' => '/contact',
            'bodyClass' => 'page-contact',
        ]);

        return (new Response($html))->withHeader('Cache-Control', 'no-store');
    }

    /**
     * @param array<string, string> $errors
     * @param array<string, mixed> $old
     */
    private function fail(array $errors, array $old = []): Response
    {
        $_SESSION[self::FLASH_KEY] = ['errors' => $errors, 'old' => array_intersect_key($old, array_flip(['name', 'email', 'company', 'services', 'message']))];

        return Response::redirect('/contact#form');
    }

    private function redirectSent(string $reference): Response
    {
        $_SESSION[self::FLASH_KEY] = ['reference' => $reference];

        return Response::redirect('/contact#sent');
    }

    /** @param array{name: string, email: string, company: string, services: list<string>, message: string} $data */
    private function summary(string $reference, array $data): string
    {
        $labels = array_map(static fn(string $s): string => ContactValidator::SERVICES[$s] ?? $s, $data['services']);

        return "New request {$reference}\n"
            . 'Name: ' . $data['name'] . "\n"
            . 'Email: ' . $data['email'] . "\n"
            . ($data['company'] !== '' ? 'Company: ' . $data['company'] . "\n" : '')
            . 'Services: ' . ($labels === [] ? '—' : implode(', ', $labels)) . "\n\n"
            . $data['message'];
    }
}
