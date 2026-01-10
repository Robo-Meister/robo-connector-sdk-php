<?php

namespace Robo\ConnectorSdk;

use Robo\ConnectorSdk\Http\ApiClient;

final class AppStoreClient
{
    private ApiClient $client;

    public function __construct(string $baseUrl, string $accessToken)
    {
        $this->client = new ApiClient($baseUrl, $accessToken);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function getPurchaseIntent(string $channel, string $name, array $query = []): array
    {
        $path = sprintf('/api/package/purchase-intent/%s/%s', rawurlencode($channel), rawurlencode($name));

        return $this->client->request('GET', $path, ['query' => $query]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function installPackage(
        string $channel,
        string $name,
        array $payload,
        array $query = [],
        ?string $idempotencyKey = null
    ): array {
        $path = sprintf('/api/package/install/%s/%s', rawurlencode($channel), rawurlencode($name));

        return $this->client->request('POST', $path, [
            'query' => $query,
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function getInstallJob(string $jobId, array $query = []): array
    {
        $path = sprintf('/api/package/install-jobs/%s', rawurlencode($jobId));

        return $this->client->request('GET', $path, ['query' => $query]);
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
