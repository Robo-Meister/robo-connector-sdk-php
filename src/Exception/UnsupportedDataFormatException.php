<?php

namespace Robo\ConnectorSdk\Exception;

use InvalidArgumentException;

final class UnsupportedDataFormatException extends InvalidArgumentException
{
    /** @param list<string> $supportedFormats */
    public function __construct(
        private readonly string $format,
        private readonly array $supportedFormats,
    ) {
        parent::__construct(sprintf(
            'Unsupported data format "%s". Supported formats: %s.',
            $format === '' ? '(empty)' : $format,
            implode(', ', $supportedFormats)
        ));
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /** @return list<string> */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }
}
