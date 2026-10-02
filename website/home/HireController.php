<?php

declare(strict_types=1);

namespace Website\Home;

use Core\Logging\Logger;
use Core\Routing\HttpException;
use Core\Routing\Request;
use Core\Routing\Response;
use Core\Security\FormGuard;
use Core\Services\LeadStore;
use Core\Services\Notifier\Notifier;

/**
 * The main page "You want us to do" work request (legacy `send_order`). Same hardened pipeline as
 * /contact; answers JSON to the page script and redirects (PRG) for plain form posts.
 */
final class HireController
{
    public const FLASH_KEY = '_hire_flash';

    public function __construct(
        private readonly FormGuard $guard,
        private readonly HireValidator $validator,
        private readonly LeadStore $leads,
        private readonly Notifier $notifier,
        private readonly Logger $logger,
    ) {}

    public function submit(Request $request): Response
    {
        $guard = $this->guard->check($request, 'hire');
        $json = $request->wantsJson();

        if ($guard['outcome'] === FormGuard::HONEYPOT) {
            return $this->success($json, 'AEV-' . strtoupper(bin2hex(random_bytes(5)))); // pretend, store nothing
        }
        if ($guard['outcome'] === FormGuard::TOO_FAST) {
            return $this->failure($json, ['form' => 'Please wait a moment and send the request again.'], 422);
        }

        $result = $this->validator->validate($request->input('name'), $request->input('email'), $request->inputList('services'));
        if ($result['errors'] !== []) {
            return $this->failure($json, $result['errors'], 422);
        }

        $data = $result['data'];
        $reference = LeadStore::newReference();

        $stored = $this->leads->append([
            'reference' => $reference,
            'created_at' => gmdate('c'),
            'source' => 'hire',
            'who' => $guard['who'],
            'name' => $data['name'],
            'email' => $data['email'],
            'services' => $data['services'],
        ]);
        if (!$stored) {
            $this->logger->error('hire.persist_failed', ['reference' => $reference]);
            throw new HttpException(500);
        }

        $labels = array_map(static fn(string $s): string => HireValidator::SERVICES[$s] ?? $s, $data['services']);
        $delivered = $this->notifier->send(
            "New work request {$reference}\nServices: " . ($labels === [] ? '—' : implode(', ', $labels))
            . "\nName: {$data['name']}\nEmail: {$data['email']}",
        );
        $this->logger->info('hire.accepted', ['reference' => $reference, 'notified' => $delivered]);

        $this->guard->rotate();

        return $this->success($json, $reference);
    }

    private function success(bool $json, string $reference): Response
    {
        if ($json) {
            return Response::json(['ok' => true, 'reference' => $reference])->withHeader('Cache-Control', 'no-store');
        }
        $_SESSION[self::FLASH_KEY] = ['ok' => true, 'reference' => $reference];

        return Response::redirect('/#hire');
    }

    /** @param array<string, string> $errors */
    private function failure(bool $json, array $errors, int $status): Response
    {
        if ($json) {
            return Response::json(['ok' => false, 'errors' => $errors], $status)->withHeader('Cache-Control', 'no-store');
        }
        $_SESSION[self::FLASH_KEY] = ['ok' => false, 'errors' => $errors];

        return Response::redirect('/#hire');
    }
}
