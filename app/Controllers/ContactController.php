<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Security\ClientIp;
use App\Security\RateLimiter;
use App\Security\Signer;
use App\Services\LeadStore;
use App\Services\Notifier\Notifier;
use App\Support\Logger;
use App\Support\View;
use App\Validation\ContactValidator;
use Core\Auth\Contracts\SessionHandlerInterface;
use Core\Auth\Security\CsrfProtection;

/**
 * Contact / work-request flow:
 * request → size limit → CSRF → honeypot → form-age check → rate limit →
 * validate/sanitise → server-side reference → persist → notify → PRG redirect.
 */
final class ContactController
{
    private const MAX_BODY_BYTES = 16384;
    private const MIN_FILL_SECONDS = 3;
    private const MAX_FORM_AGE_SECONDS = 7200;
    private const FLASH_KEY = '_contact_flash';

    public function __construct(
        private readonly View $view,
        private readonly SessionHandlerInterface $session,
        private readonly CsrfProtection $csrf,
        private readonly Signer $signer,
        private readonly RateLimiter $limiter,
        private readonly ClientIp $clientIp,
        private readonly ContactValidator $validator,
        private readonly LeadStore $leads,
        private readonly Notifier $notifier,
        private readonly Logger $logger,
    ) {}

    public function show(Request $request): Response
    {
        $this->session->start();

        $flash = $_SESSION[self::FLASH_KEY] ?? null;
        unset($_SESSION[self::FLASH_KEY]);

        return $this->page([
            'reference' => is_array($flash) ? ($flash['reference'] ?? null) : null,
            'errors' => is_array($flash) ? ($flash['errors'] ?? []) : [],
            'old' => is_array($flash) ? ($flash['old'] ?? []) : [],
        ]);
    }

    public function submit(Request $request): Response
    {
        if ($request->contentLength() > self::MAX_BODY_BYTES) {
            throw new HttpException(413);
        }

        $this->session->start();

        if (!$this->csrf->validate($request->input('_csrf'))) {
            $this->logger->warning('contact.csrf_rejected');
            throw new HttpException(403);
        }

        $ip = $this->clientIp->resolve($request);

        // Honeypot: bots fill the hidden field. Pretend success, store nothing.
        if ($request->input('website') !== '') {
            $this->logger->info('contact.honeypot', ['who' => $this->signer->pseudonym($ip)]);

            return $this->redirectSent('AEV-' . strtoupper(bin2hex(random_bytes(5))));
        }

        $rendered = $this->signer->verify($request->input('_ts'));
        $age = $rendered !== null && ctype_digit($rendered) ? time() - (int) $rendered : -1;
        if ($age < self::MIN_FILL_SECONDS || $age > self::MAX_FORM_AGE_SECONDS) {
            return $this->fail(['form' => 'Please wait a moment and submit the form again.']);
        }

        $who = $this->signer->pseudonym($ip);
        if (!$this->limiter->hit('contact:short', $ip, 3, 600) || !$this->limiter->hit('contact:day', $ip, 10, 86400)) {
            $this->logger->warning('contact.rate_limited', ['who' => $who]);
            throw new HttpException(429);
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
            'who' => $who,
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

        $this->csrf->rotateToken();

        return $this->redirectSent($reference);
    }

    /** @param array<string, mixed> $state */
    private function page(array $state): Response
    {
        $html = $this->view->render('pages/contact', $state + [
            'csrf' => $this->csrf->getToken(),
            'ts' => $this->signer->sign((string) time()),
            'services' => ContactValidator::SERVICES,
        ], [
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
