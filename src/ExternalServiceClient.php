<?php

namespace Robo\ConnectorSdk;

use Robo\ConnectorSdk\Data\DataFormat;
use Robo\ConnectorSdk\Http\ApiClient;
use Robo\ConnectorSdk\Module\MesModuleClient;
use Robo\ConnectorSdk\Module\ScmModuleClient;
use Robo\ConnectorSdk\Module\WmsModuleClient;

final class ExternalServiceClient
{
    private ApiClient $client;
    private string $apiToken;
    private ?string $productId;

    /**
     * @param array<string, string> $defaultHeaders
     */
    public function __construct(
        string $baseUrl,
        string $apiToken,
        ?string $productId = null,
        array $defaultHeaders = [],
        int $timeoutSeconds = 30,
        int $connectTimeoutSeconds = 10
    ) {
        $this->apiToken = $apiToken;
        $this->productId = $productId;
        $this->client = new ApiClient($baseUrl, $apiToken, $productId, $defaultHeaders, $timeoutSeconds, $connectTimeoutSeconds);
    }

    /** @return list<string> */
    public function supportedDataFormats(): array
    {
        return DataFormat::supported();
    }

    /**
     * Fetch a data endpoint in one of Robo Connector standard formats.
     *
     * When $format is null the API default is used. Supported explicit formats are
     * json, pdf, csv, html, and xml. A missing endpoint or endpoint that does not
     * handle the requested format raises ApiException.
     *
     * @param array<string, scalar|null> $query
     * @param array{headers?: array<string, string>, token?: string|null, product_id?: string|null, format_query?: string|null} $options
     * @return array<string, mixed>
     */
    public function fetchData(string $path, ?string $format = null, array $query = [], array $options = []): array
    {
        return $this->client->request('GET', $path, array_merge($options, [
            'query' => $query,
            'format' => $format,
        ]));
    }

    public function wms(): WmsModuleClient
    {
        return new WmsModuleClient($this->client);
    }

    public function mes(): MesModuleClient
    {
        return new MesModuleClient($this->client);
    }

    public function scm(): ScmModuleClient
    {
        return new ScmModuleClient($this->client);
    }

    /** @return array<string, mixed> */
    public function listProviders(): array
    {
        return $this->client->request('GET', '/api/integrations/providers', [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /** @return array<string, mixed> */
    public function listPaymentProviders(): array
    {
        return $this->client->request('GET', '/api/payment-providers', [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /** @return array<string, mixed> */
    public function getIntegrationProvider(string $providerId): array
    {
        return $this->client->request('GET', '/api/integrations/providers/' . rawurlencode($providerId), [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /**
     * @param array{page?: int|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function listTickets(array $query = []): array
    {
        return $this->client->request('GET', '/api/tickets', [
            'query' => $this->withTokenQuery($query),
        ]);
    }

    /** @return array<string, mixed> */
    public function getTicket(string $ticketId): array
    {
        return $this->client->request('GET', '/api/tickets/' . rawurlencode($ticketId), [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateTicket(string $ticketId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('PATCH', '/api/tickets/' . rawurlencode($ticketId), [
            'json' => $this->withTokenPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array{page?: int|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function listDocuments(array $query = []): array
    {
        return $this->client->request('GET', '/api/documents', [
            'query' => $this->withTokenQuery($query),
        ]);
    }

    /** @return array<string, mixed> */
    public function getDocument(string $documentId): array
    {
        return $this->client->request('GET', '/api/documents/' . rawurlencode($documentId), [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateDocument(string $documentId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('PATCH', '/api/documents/' . rawurlencode($documentId), [
            'json' => $this->withTokenPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array{page?: int|null, limit?: int|null} $query
     * @return array<string, mixed>
     */
    public function listInvoices(array $query = []): array
    {
        return $this->client->request('GET', '/api/invoices', [
            'query' => $this->withTokenQuery($query),
        ]);
    }

    /** @return array<string, mixed> */
    public function getInvoice(string $invoiceId): array
    {
        return $this->client->request('GET', '/api/invoices/' . rawurlencode($invoiceId), [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createInvoice(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/invoices', [
            'json' => $this->withTokenPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateInvoice(string $invoiceId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('PATCH', '/api/invoices/' . rawurlencode($invoiceId), [
            'json' => $this->withTokenPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function listAiSystems(): array
    {
        return $this->client->request('GET', '/api/ai/systems', [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /** @return array<string, mixed> */
    public function getAiSystemCredentials(string $providerId): array
    {
        return $this->client->request('GET', '/api/ai/systems/' . rawurlencode($providerId) . '/credentials', [
            'query' => $this->withTokenQuery(),
        ]);
    }

    /**
     * @param array{provider: string, client?: string|null, metadata?: array<string, mixed>|null, portal_options?: array<string, mixed>|null, token?: string|null} $payload
     * @return array<string, mixed>
     */
    public function createBillingPortalSession(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/billing/manage', [
            'json' => $this->withTokenPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function getLanguageLearningOverview(): array
    {
        return $this->client->request('GET', '/api/learning/overview');
    }

    /** @return array<string, mixed> */
    public function getBookingConfig(string $slug): array
    {
        return $this->client->request('GET', '/api/booking/' . rawurlencode($slug));
    }

    /**
     * @param array{type?: string|null, externalId?: string|null} $query
     * @return array<string, mixed>
     */
    public function getBookingCalendars(array $query = []): array
    {
        return $this->client->request('GET', '/api/booking/calendars', [
            'query' => $query,
        ]);
    }

    /** @return array<string, mixed> */
    public function getContextualBookingOptions(string $type, string|int $id): array
    {
        return $this->client->request('GET', sprintf('/api/booking/api/context/%s/%s/booking', rawurlencode($type), rawurlencode((string) $id)));
    }

    /**
     * @param array{from?: string|null, to?: string|null} $query
     * @return array<string, mixed>
     */
    public function listBookingSlots(string $slug, array $query = []): array
    {
        return $this->client->request('GET', '/public/booking/' . rawurlencode($slug) . '/slots', [
            'query' => $query,
            'token' => null,
            'product_id' => null,
        ]);
    }

    /**
     * @param array{from?: string|null, to?: string|null} $query
     * @return array<string, mixed>
     */
    public function listBookingEventSlots(string|int $eventTypeId, array $query = []): array
    {
        return $this->client->request('GET', '/public/booking/' . rawurlencode((string) $eventTypeId) . '/event_slots', [
            'query' => $query,
            'token' => null,
            'product_id' => null,
        ]);
    }

    /**
     * @param array<string, mixed> $formData
     * @return array<string, mixed>
     */
    public function submitBooking(string $slug, string $start, array $formData, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/public/booking/' . rawurlencode($slug) . '/submit.json', [
            'json' => [
                'start' => $start,
                'formData' => $formData,
            ],
            'idempotency_key' => $idempotencyKey,
            'token' => null,
            'product_id' => null,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCalendarMeet(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/universal/calendar/meet/create', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function batchCreateCalendarMeet(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/universal/calendar/meet/batch', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function queryCalendar(array $payload): array
    {
        return $this->client->request('POST', '/api/universal/calendar/query', [
            'json' => $payload,
        ]);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, mixed>
     */
    public function listWorkInbox(array $query = []): array
    {
        return $this->client->request('GET', '/api/universal/work-inbox', [
            'query' => $query,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function queryWorkInbox(array $payload): array
    {
        return $this->client->request('POST', '/api/universal/work-inbox', [
            'json' => $payload,
        ]);
    }

    /** @return array<string, mixed> */
    public function pingFlowBeacon(): array
    {
        return $this->client->request('GET', '/api/integration/flowbeacon/ping');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function ingestFlowBeaconEvent(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/flowbeacon/event', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function replayFlowBeaconEvents(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/flowbeacon/replay', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function pingFlowScribe(): array
    {
        return $this->client->request('GET', '/api/integration/flowscribe/ping');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function ingestFlowScribe(array $payload, string $flowScribeAccessToken, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/flowscribe/ingest', [
            'json' => $payload,
            'token' => $flowScribeAccessToken,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function pingAccordFlow(): array
    {
        return $this->client->request('GET', '/api/integration/accordflow/ping');
    }

    /** @return array<string, mixed> */
    public function getAccordFlowOffers(string $productId): array
    {
        return $this->client->request('GET', '/api/integration/accordflow/offers/' . rawurlencode($productId));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function preflightAccordFlow(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/accordflow/preflight', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function purchaseAccordFlow(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/accordflow/purchase', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function ingestAccordFlow(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/accordflow', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function replayAccordFlow(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/integration/accordflow/replay', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function renderDocumentTemplate(string $documentId, array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/documents/templates/' . rawurlencode($documentId) . '/render', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function renderDocumentTemplateContent(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/documents/templates/render', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function acceptDocumentOcrDraft(string $documentId, string $draftId, ?string $csrfToken = null, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', sprintf('/api/documents/%s/ocr-draft/%s/accept', rawurlencode($documentId), rawurlencode($draftId)), [
            'form' => ['_token' => $csrfToken],
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function rejectDocumentOcrDraft(string $documentId, string $draftId, ?string $csrfToken = null, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', sprintf('/api/documents/%s/ocr-draft/%s/reject', rawurlencode($documentId), rawurlencode($draftId)), [
            'form' => ['_token' => $csrfToken],
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
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
     * Queue a transactional email and link it to an optional CRM client context.
     *
     * @param array{to: string, subject: string, body: string, client?: string|null, product_id?: string|null, token?: string|null, metadata?: array<string, mixed>} $payload
     * @return array<string, mixed>
     */
    public function sendEmail(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/emails/send', [
            'json' => $this->withIntegrationPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * Subscribe a CRM contact to a communication or marketing email list.
     *
     * @param array{email?: string|null, list_id?: string|null, client_type?: string|null, link_to_all_clients_of_type?: bool|null, product_id?: string|null, token?: string|null, metadata?: array<string, mixed>} $payload
     * @return array<string, mixed>
     */
    public function addToEmailList(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/email-list', [
            'json' => $this->withIntegrationPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * Invite a participant into a communication room/call.
     *
     * @param array{to: string|int, room: string, video?: bool|null} $payload
     * @return array<string, mixed>
     */
    public function inviteCommunicationParticipant(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/communication/invite', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function upsertClient(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/clients', [
            'json' => $this->withIntegrationPayload($payload),
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
            'json' => $this->withIntegrationPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function getPaymentsObservabilityWebhooks(?int $limit = null): array
    {
        return $this->client->request('GET', '/api/payments/observability/webhooks', [
            'query' => ['limit' => $limit],
        ]);
    }

    /** @return array<string, mixed> */
    public function getPaymentsObservabilityReconciliation(?int $limit = null): array
    {
        return $this->client->request('GET', '/api/payments/observability/reconciliation', [
            'query' => ['limit' => $limit],
        ]);
    }

    /** @return array<string, mixed> */
    public function listBookings(): array
    {
        return $this->client->request('GET', '/api/booking/bookings');
    }

    /** @return array<string, mixed> */
    public function getBooking(string $id): array
    {
        return $this->client->request('GET', '/api/booking/bookings/' . rawurlencode($id));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createBooking(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/booking/bookings', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function recordPurchase(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/store/purchases', [
            'json' => $this->withIntegrationPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function recordRefund(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/store/refunds', [
            'json' => $this->withIntegrationPayload($payload),
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function listProducts(): array
    {
        return $this->client->request('GET', '/api/all_products', [
            'token' => null,
            'product_id' => null,
        ]);
    }

    /** @return array<string, mixed> */
    public function listProductOffers(string $productId): array
    {
        return $this->client->request('GET', '/api/products/' . rawurlencode($productId) . '/offers', [
            'token' => null,
            'product_id' => null,
        ]);
    }

    /** @return array<string, mixed> */
    public function listProductsOffers(): array
    {
        return $this->client->request('GET', '/api/products/offers', [
            'token' => null,
            'product_id' => null,
        ]);
    }

    /** @return array<string, mixed> */
    public function listProductVariants(string $productId): array
    {
        return $this->client->request('GET', '/api/products/' . rawurlencode($productId) . '/variants', [
            'token' => null,
            'product_id' => null,
        ]);
    }

    /** @return array<string, mixed> */
    public function listProductsVariants(): array
    {
        return $this->client->request('GET', '/api/products/variants', [
            'token' => null,
            'product_id' => null,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/orders', [
            'json' => $payload,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function getOrder(string $orderId): array
    {
        return $this->client->request('GET', '/api/orders/' . rawurlencode($orderId));
    }

    /** @return array<string, mixed> */
    public function payOrder(string $orderId, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/api/orders/' . rawurlencode($orderId) . '/pay', [
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function listWorkflowQueue(): array
    {
        return $this->client->request('GET', '/queue.json');
    }

    /** @return array<string, mixed> */
    public function acceptWorkflowQueueItem(string|int $queueItemId, ?string $idempotencyKey = null): array
    {
        return $this->client->request('POST', '/queue/accept/' . rawurlencode((string) $queueItemId) . '.json', [
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /** @return array<string, mixed> */
    public function runWorkflowSteps(string|int $templateInstantiationId, ?string $stepName = null, ?string $idempotencyKey = null): array
    {
        $path = '/run_steps/' . rawurlencode((string) $templateInstantiationId);
        if ($stepName !== null && $stepName !== '') {
            $path .= '/' . rawurlencode($stepName);
        }

        return $this->client->request('POST', $path . '.json', [
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    /**
     * @param array{query?: array<string, scalar|null>, json?: array<string, mixed>|null, form?: array<string, scalar|null>|null, headers?: array<string, string>,
     *              token?: string|null, product_id?: string|null, idempotency_key?: string|null, format?: string|null, format_query?: string|null} $options
     * @return array<string, mixed>
     */
    public function request(string $method, string $path, array $options = []): array
    {
        return $this->client->request($method, $path, $options);
    }

    /**
     * @param array<string, scalar|null> $query
     * @return array<string, scalar|null>
     */
    private function withTokenQuery(array $query = []): array
    {
        if (!array_key_exists('token', $query) && $this->apiToken !== '') {
            $query['token'] = $this->apiToken;
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function withTokenPayload(array $payload): array
    {
        if (!array_key_exists('token', $payload) && $this->apiToken !== '') {
            $payload['token'] = $this->apiToken;
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function withIntegrationPayload(array $payload): array
    {
        if (!array_key_exists('token', $payload) && $this->apiToken !== '') {
            $payload['token'] = $this->apiToken;
        }

        if (!array_key_exists('product_id', $payload) && $this->productId !== null && $this->productId !== '') {
            $payload['product_id'] = $this->productId;
        }

        return $payload;
    }
}
