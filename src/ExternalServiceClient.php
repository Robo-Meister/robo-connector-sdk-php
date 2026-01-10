<?php

namespace Robo\ConnectorSdk;

use Robo\ConnectorSdk\Http\ApiClient;

final class ExternalServiceClient
{
    private ApiClient $client;

    public function __construct(string $baseUrl, string $apiToken, ?string $productId = null)
    {
        $this->client = new ApiClient($baseUrl, $apiToken, $productId);
    }

    /** @return array<string, mixed> */
    public function listProviders(): array
    {
        return $this->client->request('GET', '/api/integrations/providers');
    }

    /** @return array<string, mixed> */
    public function listPaymentProviders(): array
    {
        return $this->client->request('GET', '/api/payment-providers');
    }

    /** @return array<string, mixed> */
    public function getLanguageLearningOverview(): array
    {
        return $this->client->request('GET', '/api/learning/overview');
    }
    public function listSocialChannels(): array
    {
        return $this->client->request('GET', '/api/social/channels');
    }

    /**
     * @param array{channel_id?: string|null, page?: int|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function listSocialPosts(array $query = []): array
    {
        return $this->client->request('GET', '/api/social/posts', [
            'query' => $query,
        ]);
    }

    /**
     * @param array{title: string, content: string, channel_id?: string|null, description?: string|null} $payload
     * @return array<string, mixed>
     */
    public function createSocialPost(array $payload): array
    {
        return $this->client->request('POST', '/api/social/posts', [
            'json' => $payload,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function upsertClient(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/clients', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createPayment(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/payments', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array{query?: array<string, scalar|null>, json?: array<string, mixed>|null, headers?: array<string, string>,
     *              token?: string, product_id?: string, idempotency_key?: string} $options
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        return $this->client->request($method, $path, $options);
    }
}
