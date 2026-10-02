<?php

declare(strict_types=1);

namespace App\Services\Http;

/**
 * cURL implementation: HTTPS only, no redirects, hard timeouts, response size cap.
 * URLs may contain API keys (query-string style providers), so exception messages
 * never include the URL.
 */
final class CurlHttpClient implements HttpClient
{
    private const MAX_BYTES = 1_048_576;

    public function get(string $url, array $headers = [], int $timeoutSeconds = 4): array
    {
        return $this->request($url, $headers, $timeoutSeconds, null);
    }

    public function postJson(string $url, string $json, array $headers = [], int $timeoutSeconds = 20): array
    {
        return $this->request($url, $headers + ['Content-Type' => 'application/json'], $timeoutSeconds, $json);
    }

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string}
     */
    private function request(string $url, array $headers, int $timeoutSeconds, ?string $body): array
    {
        if (!str_starts_with($url, 'https://')) {
            throw new HttpClientException('Only https URLs are allowed.');
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new HttpClientException('Could not initialise HTTP client.');
        }

        $received = '';
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = $name . ': ' . $value;
        }

        $options = [
            CURLOPT_HTTPHEADER => $lines,
            CURLOPT_CONNECTTIMEOUT => min(3, $timeoutSeconds),
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'aliev.io/2 (+server)',
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$received): int {
                $received .= $chunk;

                return strlen($received) > self::MAX_BYTES ? 0 : strlen($chunk); // 0 aborts the transfer
            },
        ];
        if ($body !== null) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $options);

        $ok = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($ok === false || $errno !== 0) {
            throw new HttpClientException('HTTP transport failure (curl errno ' . $errno . ').');
        }

        return ['status' => $status, 'body' => $received];
    }
}
