<?php

declare(strict_types=1);

namespace App\Services\Http;

/** Minimal outbound HTTP seam so providers can be tested without the network. */
interface HttpClient
{
    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string}
     * @throws HttpClientException on transport failure (DNS, TLS, timeout, oversize)
     */
    public function get(string $url, array $headers = [], int $timeoutSeconds = 4): array;

    /**
     * @param array<string, string> $headers
     * @return array{status: int, body: string}
     * @throws HttpClientException on transport failure
     */
    public function postJson(string $url, string $json, array $headers = [], int $timeoutSeconds = 20): array;
}
