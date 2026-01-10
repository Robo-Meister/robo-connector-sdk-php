# Robo Connector SDK (PHP)

Lightweight PHP SDK for the Robo Connector APIs. This package is framework-free and designed to be Packagist-friendly.

## Install

```bash
composer require robo/robo-connector-sdk
```

## App Store usage

```php
use Robo\ConnectorSdk\AppStoreClient;

$client = new AppStoreClient(
    baseUrl: 'https://app.robo.dev',
    accessToken: 'app-store-user-token'
);

$intent = $client->getPurchaseIntent('marketplace', 'flowbeacon-ai', [
    'version' => '1.2.3',
]);

$install = $client->installPackage(
    'marketplace',
    'flowbeacon-ai',
    $intent['install_request']['payload'],
    idempotencyKey: 'install-'.bin2hex(random_bytes(8))
);

$status = $client->getInstallJob($install['job_id']);
```

## External service usage (Integration API)

```php
use Robo\ConnectorSdk\ExternalServiceClient;

$client = new ExternalServiceClient(
    baseUrl: 'https://app.robo.dev',
    apiToken: 'organisation-api-token',
    productId: 'flowbeacon-ai'
);

$providers = $client->listProviders();

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

## Notes

- The App Store endpoints use a **user access token** (OAuth) or a system token with equivalent permissions.
- The Integration API uses an **organisation API token** and often requires `X-Product-ID` or `product_id`.
- POST/PATCH endpoints require an `Idempotency-Key`. The SDK exposes this as an optional argument.
- Use the `request()` method on each client if you need to call an endpoint that is not wrapped yet.

