<?php

namespace Robo\ConnectorSdk\Module;

use Robo\ConnectorSdk\Http\ApiClient;

final class ScmModuleClient
{
    public function __construct(private ApiClient $client)
    {
    }

    /** @return array<string, mixed> */
    public function getDashboard(): array
    {
        return $this->client->request('GET', '/scm/dashboard.json');
    }

    /** @return array<string, mixed> */
    public function getSupplyChainFlow(): array
    {
        return $this->client->request('GET', '/scm/supply_chain/flow.json');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function simulateSupplyChainDelay(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/scm/supply_chain/simulate-delay.json', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function assignCarrier(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/scm/carrier-selection/assign.json', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function getCarrierSelection(array $query = []): array
    {
        return $this->client->request('GET', '/scm/carrier-selection.json', [
            'query' => $query,
        ]);
    }

    /** @return array<string, mixed> */
    public function getSupplierKpis(): array
    {
        return $this->client->request('GET', '/scm/kpi/supplier.json');
    }

    /** @return array<string, mixed> */
    public function getKpiDashboard(): array
    {
        return $this->client->request('GET', '/scm/kpi/dashboard.json');
    }

    /** @return array<string, mixed> */
    public function getSupplierRiskSnapshot(): array
    {
        return $this->client->request('GET', '/scm/risk/supplier-snapshot.json');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function autoReorderProcurement(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/scm/procurement/auto-reorder.json', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function getTrackingHistory(): array
    {
        return $this->client->request('GET', '/package_tracking/history.json');
    }

    /** @return array<string, mixed> */
    public function getTrackingStatuses(string $code): array
    {
        return $this->client->request('GET', '/package_tracking/api/statuses/' . rawurlencode($code));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function ingestGps(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/package_tracking/ingest/gps', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateTrackingStatus(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/package_tracking/api/update', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createTransportEvent(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/scm/transport/events', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function getFullRoutePlan(string $code): array
    {
        return $this->client->request('GET', '/route_plan/full/' . rawurlencode($code) . '.json');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function generateRoute(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/route_plan/api/route/generate', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function previewRoute(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/route_plan/api/route-preview', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function getPortSlotSelection(array $query = []): array
    {
        return $this->client->request('GET', '/api/port/slot-selection', [
            'query' => $query,
        ]);
    }

    /** @return array<string, mixed> */
    public function getTransportDelivery(string $assignmentId): array
    {
        return $this->client->request('GET', '/api/transport/deliveries/' . rawurlencode($assignmentId));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateTransportShuttle(string $assignmentId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/transport/shuttles/' . rawurlencode($assignmentId), [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }
}
