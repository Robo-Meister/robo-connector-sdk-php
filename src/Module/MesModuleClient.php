<?php

namespace Robo\ConnectorSdk\Module;

use Robo\ConnectorSdk\Http\ApiClient;

final class MesModuleClient
{
    public function __construct(private ApiClient $client)
    {
    }

    /** @return array<string, mixed> */
    public function getProductionLineProgram(string|int $productionLineId): array
    {
        return $this->client->request('GET', '/production_line/' . rawurlencode((string) $productionLineId) . '/program.json');
    }

    /** @return array<string, mixed> */
    public function getProductionLineMap(string|int $productionLineId): array
    {
        return $this->client->request('GET', '/production_line/show-map/' . rawurlencode((string) $productionLineId) . '.json');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function queueProductionLineTask(string|int $productionLineId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/production_line/' . rawurlencode((string) $productionLineId) . '/queue-task', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function listMachines(): array
    {
        return $this->client->request('GET', '/machine/.json');
    }

    /** @return array<string, mixed> */
    public function listProductionSchedules(): array
    {
        return $this->client->request('GET', '/production_schedule/.json');
    }

    /** @return array<string, mixed> */
    public function getProductionSchedule(string|int $scheduleId): array
    {
        return $this->client->request('GET', '/production_schedule/' . rawurlencode((string) $scheduleId) . '.json');
    }

    /** @return array<string, mixed> */
    public function listBillOfMaterials(): array
    {
        return $this->client->request('GET', '/bill_of_materials/.json');
    }

    /** @return array<string, mixed> */
    public function listRobotAlerts(): array
    {
        return $this->client->request('GET', '/robots/alerts.json');
    }
}
