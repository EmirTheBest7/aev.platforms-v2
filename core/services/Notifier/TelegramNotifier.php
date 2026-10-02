<?php

declare(strict_types=1);

namespace Core\Services\Notifier;

use Core\Logging\Logger;

/**
 * Telegram Bot API delivery. Credentials come from the environment only; the
 * token is never logged (the Logger additionally scrubs token-shaped strings),
 * requests are HTTPS-only with hard timeouts, and failures are swallowed so a
 * Telegram outage can never break a form submission.
 */
final class TelegramNotifier implements Notifier
{
    public function __construct(
        private readonly string $token,
        private readonly string $chatId,
        private readonly int $timeoutSeconds,
        private readonly Logger $logger,
        private readonly string $apiBase = 'https://api.telegram.org',
    ) {}

    public function send(string $text): bool
    {
        if ($this->token === '' || $this->chatId === '') {
            $this->logger->warning('notify.telegram.unconfigured');

            return false;
        }

        $ch = curl_init($this->apiBase . '/bot' . $this->token . '/sendMessage');
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'chat_id' => $this->chatId,
                'text' => mb_substr($text, 0, 3500),
                'disable_web_page_preview' => 'true',
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => min(2, $this->timeoutSeconds),
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS | CURLPROTO_HTTP, // http only reachable via explicit apiBase (tests)
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_errno($ch) !== 0 ? 'curl_errno=' . curl_errno($ch) : '';
        curl_close($ch);

        if ($body === false || $status !== 200) {
            $this->logger->warning('notify.telegram.failed', ['http_status' => $status, 'error' => $error]);

            return false;
        }

        return true;
    }
}
