# Robo Connector SDK (PHP)

Lightweight PHP SDK for Robo Connector APIs. This package is framework-free and Packagist-friendly.

## Install

```bash
composer require robo/robo-connector-sdk
```

## Coverage snapshot (SDK vs routes vs OpenAPI)

Current repo state:

- `public/openapi.yml` currently documents only `/api/login`.
- `public/openapi2.yml` currently documents only `/api/reports/monthly`.
- Most operational API surface exists in Symfony controllers/routes and is wrapped by this SDK.

| Area | Route/controller availability | OpenAPI (`public/openapi*.yml`) | PHP SDK |
|---|---|---|---|
| App Store install flow | ✅ | ❌ | ✅ |
| Integration providers/payments | ✅ | ❌ | ✅ |
| Tickets/Documents/Invoices integration resources | ✅ | ❌ | ✅ |
| AI systems + billing portal | ✅ | ❌ | ✅ |
| Booking (public + private) | ✅ | ❌ | ✅ |
| Booking CRUD (`/api/booking/bookings`) | ✅ | ❌ | ✅ |
| Calendar meet/query | ✅ | ❌ | ✅ |
| Documents template/OCR review | ✅ | ❌ | ✅ |
| Social demo endpoints | ✅ | ❌ | ✅ |
| Orders + product catalogue + store refunds | ✅ | ❌ | ✅ |
| Payment observability endpoints | ✅ | ❌ | ✅ |
| WMS movement/scan/delivery module | ✅ | ❌ | ✅ |
| MES production-line module | ✅ | ❌ | ✅ |
| SCM transport/procurement/routing module | ✅ | ❌ | ✅ |
| CRM client registration + email-list membership | ✅ | ❌ | ✅ |
| Communication email queue + room invites | ✅ | ❌ | ✅ |
| BPM/work inbox + FlowBeacon/FlowScribe/AccordFlow | ✅ | ❌ | ✅ |

If you need a non-wrapped endpoint, use `request()` directly and keep `Idempotency-Key` for write operations.

## Authentication model

### App Store endpoints
Use a user OAuth access token (or equivalent system token).

```php
use Robo\ConnectorSdk\AppStoreClient;

$client = new AppStoreClient(
    baseUrl: 'https://robo-meister.com',
    accessToken: 'app-store-user-token'
);
```

### Integration API endpoints
Use organisation API token (and optionally `X-Product-ID`). Typed CRM, communication, payment, and email-list write helpers also add `token` and `product_id` into JSON bodies when the endpoint requires those fields and you did not pass explicit values.

```php
use Robo\ConnectorSdk\ExternalServiceClient;

$client = new ExternalServiceClient(
    baseUrl: 'https://robo-meister.com',
    apiToken: 'organisation-api-token',
    productId: 'flowbeacon-ai'
);
```

## Transport tuning (timeouts/headers)

Both SDK clients allow optional transport tuning for production hardening. Defaults include `Accept: application/json` and a package-level `User-Agent`.

```php
$client = new ExternalServiceClient(
    baseUrl: 'https://robo-meister.com',
    apiToken: 'organisation-api-token',
    productId: 'flowbeacon-ai',
    defaultHeaders: [
        'X-Correlation-ID' => 'req-123',
    ],
    timeoutSeconds: 45,
    connectTimeoutSeconds: 10,
);

$appStore = new AppStoreClient(
    baseUrl: 'https://robo-meister.com',
    accessToken: 'app-store-user-token',
    defaultHeaders: [
        'X-Correlation-ID' => 'req-123',
    ],
    timeoutSeconds: 45,
    connectTimeoutSeconds: 10,
);
```

## App Store usage

Use `AppStoreClient` for package discovery/install APIs. Do not reuse `ExternalServiceClient` for these methods.

```php
$intent = $appStore->getPurchaseIntent('marketplace', 'flowbeacon-ai', [
    'version' => '1.2.3',
]);

$install = $appStore->installPackage(
    'marketplace',
    'flowbeacon-ai',
    $intent['install_request']['payload'],
    idempotencyKey: 'install-'.bin2hex(random_bytes(8))
);

$status = $appStore->getInstallJob($install['job_id']);

$checkoutSession = $appStore->createCheckoutSession([
    'user_id' => 'user-123',
    'owner_type' => 'workspace',
    'owner_id' => 'workspace-123',
    'organisation_id' => 'org-123',
    'selected_offer_sku' => 'ROBO_CONNECTOR_TICKET_LTD_SOLO',
    'currency' => 'USD',
], idempotencyKey: 'checkout-'.bin2hex(random_bytes(8)));
```

## Standard data format selection

All PHP SDK requests can optionally ask Robo Connector data endpoints for a
standard response format. The SDK normalizes and validates the format, sends the
matching `Accept` header, and adds `?format=<format>` unless you override the
query parameter name with `format_query`.

Supported formats are `json`, `pdf`, `csv`, `html`, and `xml`.

Use `fetchData()` as the shared base for read-only data calls:

```php
$csvReport = $client->fetchData('/api/reports/monthly', 'csv', [
    'month' => '2026-05',
]);

$pdfInvoice = $client->fetchData('/api/invoices/invoice-id/export', 'pdf');

$defaultFormat = $client->fetchData('/api/reports/monthly'); // no format override
```

For custom or newly-added endpoints, `request()` accepts the same optional
format controls:

```php
$xmlPayload = $client->request('GET', '/api/data-feed', [
    'format' => 'xml',
    'format_query' => 'output', // optional; defaults to "format"
]);
```

Unsupported local format values throw
`Robo\ConnectorSdk\Exception\UnsupportedDataFormatException`. If the server
returns `404`, `406`, or `415` for a formatted data request, the SDK raises
`ApiException` with a message that the endpoint does not exist or does not handle
the requested format.

## Integration usage by scenario

### 1) Provider discovery, clients, and payments

```php
$providers = $client->listProviders();
$paymentProviders = $client->listPaymentProviders();

$clientData = $client->upsertClient([
    'email' => 'customer@example.com',
    'name' => 'Customer Name',
    'product_id' => 'flowbeacon-ai',
], idempotencyKey: 'client-'.bin2hex(random_bytes(8)));

$payment = $client->createPayment([
    'product_id' => 'flowbeacon-ai',
    'provider' => 'stripe',
    'amount' => 249.00,
    'currency' => 'EUR',
    'client' => 'customer@example.com',
    'metadata' => ['source' => 'integration-demo'],
], idempotencyKey: 'payment-'.bin2hex(random_bytes(8)));
```

### 2) Tickets, documents, invoices, AI systems, and billing

```php
$tickets = $client->listTickets(['page' => 1, 'limit' => 25]);
$ticket = $client->getTicket('ticket-id');
$updatedTicket = $client->updateTicket('ticket-id', [
    'status' => 'resolved',
], idempotencyKey: 'ticket-'.bin2hex(random_bytes(8)));

$documents = $client->listDocuments(['page' => 1, 'limit' => 25]);
$document = $client->getDocument('document-id');
$updatedDocument = $client->updateDocument('document-id', [
    'status' => 'approved',
], idempotencyKey: 'document-'.bin2hex(random_bytes(8)));

$invoice = $client->createInvoice([
    'customer' => 'customer@example.com',
    'lines' => [
        ['description' => 'Subscription', 'amount' => 249.00, 'currency' => 'EUR'],
    ],
], idempotencyKey: 'invoice-'.bin2hex(random_bytes(8)));

$invoices = $client->listInvoices(['page' => 1, 'limit' => 25]);
$updatedInvoice = $client->updateInvoice($invoice['invoice']['id'], [
    'status' => 'sent',
]);

$aiSystems = $client->listAiSystems();
$credentials = $client->getAiSystemCredentials('provider-id');

$billingPortal = $client->createBillingPortalSession([
    'provider' => 'stripe',
    'client' => 'customer@example.com',
    'portal_options' => ['return_url' => 'https://example.com/account'],
]);
```

### 3) Product catalogue, order lifecycle, purchases, and refunds

```php
$products = $client->listProducts();
$offers = $client->listProductOffers('flowbeacon-ai');
$variants = $client->listProductVariants('flowbeacon-ai');

$order = $client->createOrder([
    'productId' => 'flowbeacon-ai',
    'offerId' => 'offer-id',
    'ownerType' => 'organisation',
    'ownerId' => 'org-id',
], idempotencyKey: 'order-'.bin2hex(random_bytes(8)));

$paid = $client->payOrder($order['id'], idempotencyKey: 'pay-'.bin2hex(random_bytes(8)));

$refund = $client->recordRefund([
    'product_id' => 'flowbeacon-ai',
    'client' => 'customer@example.com',
    'amount' => 49.00,
    'currency' => 'EUR',
], idempotencyKey: 'refund-'.bin2hex(random_bytes(8)));
```

### 4) Booking/public scheduling and internal booking records

```php
$bookingConfig = $client->getBookingConfig('intro-call');
$slots = $client->listBookingSlots('intro-call', [
    'from' => '2026-01-01T00:00:00Z',
    'to' => '2026-01-07T00:00:00Z',
]);

$submitted = $client->submitBooking(
    'intro-call',
    '2026-01-03T10:00:00Z',
    [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ],
    idempotencyKey: 'booking-submit-'.bin2hex(random_bytes(8))
);

$createdBooking = $client->createBooking([
    'resourceName' => 'Room A',
    'resourceId' => 'room-a',
    'start' => '2026-01-03T10:00:00Z',
    'end' => '2026-01-03T10:30:00Z',
    'status' => 'pending',
    'metadata' => ['source' => 'sdk-demo'],
], idempotencyKey: 'booking-create-'.bin2hex(random_bytes(8)));

$bookings = $client->listBookings();
```

### 5) Calendar meets and query

```php
$meet = $client->createCalendarMeet([
    'title' => 'Intro call',
    'starts_at' => '2026-01-03T10:00:00Z',
]);

$batch = $client->batchCreateCalendarMeet([
    'items' => [
        ['title' => 'Call 1', 'starts_at' => '2026-01-03T11:00:00Z'],
        ['title' => 'Call 2', 'starts_at' => '2026-01-03T12:00:00Z'],
    ],
]);

$query = $client->queryCalendar([
    'from' => '2026-01-01T00:00:00Z',
    'to' => '2026-01-31T23:59:59Z',
]);
```

### 6) Document templates and OCR review

```php
$rendered = $client->renderDocumentTemplateContent([
    'content' => 'Agreement for %%client_name%%',
    'fields' => ['client_name' => 'Ada Lovelace'],
], idempotencyKey: 'doc-render-'.bin2hex(random_bytes(8)));

$accepted = $client->acceptDocumentOcrDraft(
    'document-id',
    'draft-id',
    csrfToken: 'csrf-token-from-session',
    idempotencyKey: 'ocr-accept-'.bin2hex(random_bytes(8))
);
```

### 7) Social posting

```php
$channels = $client->listSocialChannels();
$posts = $client->listSocialPosts(['page' => 1, 'limit' => 20]);

$created = $client->createSocialPost([
    'title' => 'Launch update',
    'content' => 'We just shipped a new integration.',
    'description' => 'Release note post',
]);
```

### 8) Payment observability and reconciliation

```php
$webhooks = $client->getPaymentsObservabilityWebhooks(limit: 50);
$reconciliation = $client->getPaymentsObservabilityReconciliation(limit: 50);
```

### 9) WMS, MES, and SCM module clients

The integration client exposes module clients for warehouse, manufacturing, and supply-chain workflows. These are thin wrappers over the existing Symfony routes and keep the same authentication, timeout, default-header, and idempotency behavior as `ExternalServiceClient`.

```php
$wms = $client->wms();
$mes = $client->mes();
$scm = $client->scm();

$palletLedger = $wms->getPalletMovementLedger('pallet-id', [
    'organizationId' => 123,
    'limit' => 100,
]);

$scanCard = $wms->resolveScan('signed-scan-code', mode: 'inbound');
$scanResult = $wms->actOnScan(
    'signed-scan-code',
    'putaway',
    ['sectionCode' => 'A1-01-01'],
    mode: 'inbound',
    idempotencyKey: 'scan-'.bin2hex(random_bytes(8))
);

$program = $mes->getProductionLineProgram('line-id');
$lineMap = $mes->getProductionLineMap('line-id');
$queued = $mes->queueProductionLineTask(
    'line-id',
    ['task' => 'rebalance', 'priority' => 'high'],
    idempotencyKey: 'mes-task-'.bin2hex(random_bytes(8))
);

$flow = $scm->getSupplyChainFlow();
$carrier = $scm->assignCarrier(
    ['routePlanId' => 'route-plan-id'],
    idempotencyKey: 'carrier-'.bin2hex(random_bytes(8))
);
$transportEvent = $scm->createTransportEvent([
    'reference' => 'BOOKING-123',
    'status' => 'in_transit',
    'occurredAt' => '2026-05-07T12:00:00Z',
]);
```

Wrapped module surfaces include:

- **WMS**: movement ledger by pallet/section/range, scan resolve/action, driver confirmation, delivery list/detail.
- **MES**: production-line program/map/task queueing, machine list, production schedules, bill of materials, robot alerts.
- **SCM**: dashboard, supply-chain graph and delay simulation, carrier selection/assignment, KPI and supplier risk feeds, procurement auto-reorder, tracking GPS/status/history, transport events, route plans, port slot selection, and transport assignment updates.
### 10) CRM client lifecycle and marketing list membership

```php
$crmClient = $client->upsertClient([
    'email' => 'ada@example.com',
    'name' => 'Ada Lovelace',
    'phone' => '+48 123 456 789',
    'metadata' => ['segment' => 'founder'],
], idempotencyKey: 'crm-client-'.bin2hex(random_bytes(8)));

$listSubscription = $client->addToEmailList([
    'email' => 'ada@example.com',
    'list_id' => 'newsletter-founders',
    'metadata' => ['source' => 'php-sdk'],
], idempotencyKey: 'crm-list-'.bin2hex(random_bytes(8)));

$clientTypeSubscription = $client->addToEmailList([
    'client_type' => 'accounting-lead',
    'link_to_all_clients_of_type' => true,
    'list_id' => 'accounting-nurture',
]);
```

### 11) Communication email and live-room helpers

```php
$email = $client->sendEmail([
    'to' => 'ada@example.com',
    'client' => 'ada@example.com',
    'subject' => 'Welcome to Robo Connector',
    'body' => 'Your workspace is ready.',
], idempotencyKey: 'email-'.bin2hex(random_bytes(8)));

$invite = $client->inviteCommunicationParticipant([
    'to' => 'ada@example.com',
    'room' => 'support-room-123',
    'video' => true,
]);
```

### 12) BPM/work inbox and flow integrations

```php
$workInbox = $client->listWorkInbox([
    'limit' => 25,
    'module' => 'bpm',
]);

$filteredInbox = $client->queryWorkInbox([
    'filters' => ['priority' => 'high'],
    'collectors' => ['bpm_steps', 'crm_tasks'],
]);

$flowBeaconHealth = $client->pingFlowBeacon();
$flowEvent = $client->ingestFlowBeaconEvent([
    'event' => 'workflow.step.completed',
    'workflow_id' => 'wf-123',
    'step_id' => 'approval',
], idempotencyKey: 'flow-event-'.bin2hex(random_bytes(8)));

$flowScribe = $client->ingestFlowScribe([
    'document_id' => 'doc-123',
    'workflow_id' => 'wf-123',
    'content' => 'Approval summary',
]);

$accordPreflight = $client->preflightAccordFlow([
    'product_id' => 'accordflow',
    'workflow' => ['type' => 'contract-review'],
]);

$queue = $client->listWorkflowQueue();
$accepted = $client->acceptWorkflowQueueItem('queue-item-id');
$run = $client->runWorkflowSteps('template-instantiation-id');
```

## Error handling

HTTP 4xx/5xx responses throw `Robo\ConnectorSdk\Exception\ApiException`.

```php
use Robo\ConnectorSdk\Exception\ApiException;

try {
    $client->listProviders();
} catch (ApiException $error) {
    printf("status=%d\n", $error->getStatusCode());
    var_dump($error->getResponse());
    var_dump($error->getRawResponseBody());
}
```

## Notes

- App Store endpoints use user-level OAuth access tokens.
- Integration API endpoints use organisation API token and often require `product_id` or `X-Product-ID`.
- Use `Idempotency-Key` for `POST`/`PATCH` write operations.
- Use `request()` when you need a new endpoint before a typed helper is added.
