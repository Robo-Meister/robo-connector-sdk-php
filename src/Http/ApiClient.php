<?php

namespace Robo\ConnectorSdk\Http;

use JsonException;
use Robo\ConnectorSdk\Data\DataFormat;
use Robo\ConnectorSdk\Exception\ApiException;
use RuntimeException;

final class ApiClient
{
    private const DEFAULT_TIMEOUT_SECONDS = 30;
    private const DEFAULT_CONNECT_TIMEOUT_SECONDS = 10;
    private const DEFAULT_USER_AGENT = 'robo-connector-sdk-php/1.0';

    /** @var array<string, string> */
    private array $defaultHeaders;

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $token = null,
        private readonly ?string $productId = null,
        array $defaultHeaders = [],
        private readonly int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
        private readonly int $connectTimeoutSeconds = self::DEFAULT_CONNECT_TIMEOUT_SECONDS,
    ) {
        $this->defaultHeaders = $this->normalizeHeaders(array_merge([
            'Accept' => 'application/json',
            'User-Agent' => self::DEFAULT_USER_AGENT,
        ], $defaultHeaders));
    }

    /**
     * @param array{query?: array<string, scalar|null>, json?: array<string, mixed>|null, form?: array<string, scalar|null>|null, headers?: array<string, string>,
     *              token?: string|null, product_id?: string|null, idempotency_key?: string|null, format?: string|null, format_query?: string|null} $options
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $query = $options['query'] ?? [];
        $payload = $options['json'] ?? null;
        $formPayload = $options['form'] ?? null;
        $headers = $this->defaultHeaders;
        $format = DataFormat::normalize($options['format'] ?? null);
        if ($format !== null) {
            $headers['Accept'] = DataFormat::acceptHeader($format);
            $formatQueryParameter = $options['format_query'] ?? 'format';
            if (is_string($formatQueryParameter) && $formatQueryParameter !== '' && !array_key_exists($formatQueryParameter, $query)) {
                $query[$formatQueryParameter] = $format;
            }
        }

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
                $headers[$this->normalizeHeaderName($key)] = $value;
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
        if ($formPayload !== null && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->connectTimeoutSeconds);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));

        if ($payload !== null) {
            try {
                $body = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException('Unable to encode JSON payload: ' . $exception->getMessage(), previous: $exception);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($formPayload !== null) {
            $formBody = http_build_query(array_filter($formPayload, static fn ($value) => $value !== null), '', '&', PHP_QUERY_RFC3986);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $formBody);
        }

        $responseBody = curl_exec($curl);
        if ($responseBody === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new RuntimeException(sprintf('HTTP request failed (%s %s): %s', $method, $path, $error));
        }

        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = $this->decodeResponse($responseBody);
        if ($status >= 400) {
            $message = is_array($decoded) ? (string) ($decoded['message'] ?? 'API request failed.') : 'API request failed.';
            if ($format !== null && in_array($status, [404, 406, 415], true)) {
                $message = sprintf('Data endpoint %s does not exist or does not handle %s format.', $path, $format);
            }
            throw new ApiException($message, $status, is_array($decoded) ? $decoded : null, $responseBody);
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

        try {
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /** @param array<string, string> $headers */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[$this->normalizeHeaderName($key)] = $value;
        }

        return $normalized;
    }

    private function normalizeHeaderName(string $header): string
    {
        return implode('-', array_map(
            static fn (string $part): string => ucfirst(strtolower($part)),
            explode('-', $header)
        ));
    }
}
