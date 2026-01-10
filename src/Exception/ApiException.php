<?php

namespace Robo\ConnectorSdk\Exception;

use RuntimeException;

final class ApiException extends RuntimeException
{
    /** @var array<string, mixed>|null */
    private ?array $response;

    /** @param array<string, mixed>|null $response */
    public function __construct(string $message, private readonly int $statusCode, ?array $response = null)
    {
        parent::__construct($message, $statusCode);
        $this->response = $response;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** @return array<string, mixed>|null */
    public function getResponse(): ?array
    {
        return $this->response;
    }
}
