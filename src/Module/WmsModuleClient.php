<?php

namespace Robo\ConnectorSdk\Module;

use Robo\ConnectorSdk\Http\ApiClient;

final class WmsModuleClient
{
    public function __construct(private ApiClient $client)
    {
    }

    /**
     * @param array{organizationId?: int|string|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function getPalletMovementLedger(string|int $palletId, array $query = []): array
    {
        return $this->client->request('GET', '/wms/movement-ledger/pallet/' . rawurlencode((string) $palletId) . '.json', [
            'query' => $query,
        ]);
    }

    /**
     * @param array{organizationId?: int|string|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function getSectionMovementLedger(string $sectionCode, array $query = []): array
    {
        return $this->client->request('GET', '/wms/movement-ledger/section/' . rawurlencode($sectionCode) . '.json', [
            'query' => $query,
        ]);
    }

    /**
     * @param array{organizationId?: int|string|null, from?: string|null, to?: string|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function getMovementLedgerRange(array $query = []): array
    {
        return $this->client->request('GET', '/wms/movement-ledger/range.json', [
            'query' => $query,
        ]);
    }

    /** @return array<string, mixed> */
    public function resolveScan(string $code, ?string $mode = null): array
    {
        return $this->client->request('GET', '/scan/resolve', [
            'query' => [
                'code' => $code,
                'mode' => $mode,
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    public function actOnScan(string $code, string $action, array $extra = [], ?string $mode = null, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/scan/act', [
            'json' => [
                'code' => $code,
                'action' => $action,
                'extra' => $extra,
                'mode' => $mode,
            ],
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function confirmDriverScan(string $code, string $action = 'confirm_delivery', ?string $note = null, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/scan/driver/confirm', [
            'form' => [
                'code' => $code,
                'action' => $action,
                'note' => $note,
            ],
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function listDeliveries(): array
    {
        return $this->client->request('GET', '/delivery/list.json');
    }

    /** @return array<string, mixed> */
    public function getDelivery(string|int $deliveryId): array
    {
        return $this->client->request('GET', '/delivery/show/' . rawurlencode((string) $deliveryId) . '.json');
    }
}
