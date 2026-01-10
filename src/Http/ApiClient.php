<?php

namespace Robo\ConnectorSdk\Http;

use Robo\ConnectorSdk\Exception\ApiException;
use RuntimeException;

final class ApiClient
{
    /** @var array<string, string> */
    private array $defaultHeaders;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly ?string $productId = null,
        array $defaultHeaders = []
    ) {
        $this->defaultHeaders = $defaultHeaders;
    }

    /**
     * @param array{query?: array<string, scalar|null>, json?: array<string, mixed>|null, headers?: array<string, string>,
     *              token?: string, product_id?: string, idempotency_key?: string} $options
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $query = $options['query'] ?? [];
        $payload = $options['json'] ?? null;
        $headers = $this->defaultHeaders;

        $token = $options['token'] ?? $this->token;
        if (is_string($token) && $token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $productId = $options['product_id'] ?? $this->productId;
        if (is_string($productId) && $productId !== '') {
            $headers['X-Product-ID'] = $productId;
        }

        $idempotencyKey = $options['idempotency_key'] ?? null;
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        if (is_array($options['headers'] ?? null)) {
            foreach ($options['headers'] as $key => $value) {
                $headers[$key] = $value;
            }
        }

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');
        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize HTTP client.');
        }

        $method = strtoupper($method);
        if ($payload !== null && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));

        if ($payload !== null) {
            $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
            if ($body === false) {
                throw new RuntimeException('Unable to encode JSON payload.');
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($curl);
        if ($responseBody === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException(sprintf('HTTP request failed: %s', $error));
        }

        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = $this->decodeResponse($responseBody);
        if ($status >= 400) {
            $message = is_array($decoded) ? (string) ($decoded['message'] ?? 'API request failed.') : 'API request failed.';
            throw new ApiException($message, $status, is_array($decoded) ? $decoded : null);
        }

        return is_array($decoded) ? $decoded : ['raw' => $responseBody];
    }

    /** @return array<int, string> */
    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = $key . ': ' . $value;
        }
        return $formatted;
    }

    /** @return array<string, mixed>|null */
    private function decodeResponse(string $responseBody): ?array
    {
        if ($responseBody === '') {
            return [];
        }

        $decoded = json_decode($responseBody, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return null;
    }
}
