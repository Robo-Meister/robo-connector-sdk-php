<?php

namespace Robo\AuthSdk;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use RuntimeException;

final class RoboAuthSdk
{
    private array $cachedKeys = [];
    private ?int $cacheExpiresAt = null;

    public function __construct(
        private readonly string $issuer,
        private readonly string $audience,
        private readonly string $jwksUrl,
        private readonly int $cacheTtlSeconds = 86400
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyAccessToken(string $jwt): array
    {
        $keys = $this->getKeys();
        $payload = (array) JWT::decode($jwt, $keys);

        if (($payload['iss'] ?? null) !== $this->issuer) {
            throw new RuntimeException('Invalid token issuer.');
        }

        $aud = $payload['aud'] ?? null;
        if ($aud !== $this->audience && (!is_array($aud) || !in_array($this->audience, $aud, true))) {
            throw new RuntimeException('Invalid token audience.');
        }

        $required = ['sub', 'org_id', 'roles', 'scopes'];
        foreach ($required as $claim) {
            if (!isset($payload[$claim])) {
                throw new RuntimeException(sprintf('Missing claim %s.', $claim));
            }
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function verifyIntentContext(string $jwt, string $expectedOrgId): array
    {
        $payload = (array) JWT::decode($jwt, $this->getKeys());

        foreach (['intent_id', 'org_id', 'issued_at', 'expires_at'] as $claim) {
            if (!isset($payload[$claim])) {
                throw new RuntimeException(sprintf('Missing claim %s.', $claim));
            }
        }

        if (($payload['org_id'] ?? null) !== $expectedOrgId) {
            throw new RuntimeException('Intent org_id mismatch.');
        }

        if ((int) ($payload['expires_at'] ?? 0) < time()) {
            throw new RuntimeException('Intent context expired.');
        }

        return $payload;
    }

    public function isReturnToAllowed(string $returnTo, array $allowedOrigins): bool
    {
        $host = parse_url($returnTo, PHP_URL_HOST);
        if (!is_string($host)) {
            return false;
        }

        foreach ($allowedOrigins as $origin) {
            $originHost = parse_url((string) $origin, PHP_URL_HOST);
            if (is_string($originHost) && strcasecmp($originHost, $host) === 0) {
                return true;
            }
        }

        return false;
    }

    private function getKeys(): array
    {
        if ($this->cacheExpiresAt !== null && time() < $this->cacheExpiresAt) {
            return $this->cachedKeys;
        }

        $jwksJson = file_get_contents($this->jwksUrl);
        if ($jwksJson === false || $jwksJson === '') {
            throw new RuntimeException('Unable to fetch JWKS.');
        }

        $payload = json_decode($jwksJson, true);
        if (!is_array($payload)) {
            throw new RuntimeException('Invalid JWKS payload.');
        }

        $this->cachedKeys = JWK::parseKeySet($payload);
        $this->cacheExpiresAt = time() + $this->cacheTtlSeconds;

        return $this->cachedKeys;
    }
}
