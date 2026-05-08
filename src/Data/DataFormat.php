<?php

namespace Robo\ConnectorSdk\Data;

use Robo\ConnectorSdk\Exception\UnsupportedDataFormatException;

final class DataFormat
{
    public const JSON = 'json';
    public const PDF = 'pdf';
    public const CSV = 'csv';
    public const HTML = 'html';
    public const XML = 'xml';

    /** @var array<string, string> */
    private const ACCEPT_HEADERS = [
        self::JSON => 'application/json',
        self::PDF => 'application/pdf',
        self::CSV => 'text/csv',
        self::HTML => 'text/html',
        self::XML => 'application/xml, text/xml;q=0.9',
    ];

    /** @return list<string> */
    public static function supported(): array
    {
        return array_keys(self::ACCEPT_HEADERS);
    }

    public static function normalize(?string $format): ?string
    {
        if ($format === null || trim($format) === '') {
            return null;
        }

        $normalized = strtolower(ltrim(trim($format), '.'));
        if (!isset(self::ACCEPT_HEADERS[$normalized])) {
            throw new UnsupportedDataFormatException($normalized, self::supported());
        }

        return $normalized;
    }

    public static function acceptHeader(string $format): string
    {
        $normalized = self::normalize($format);
        if ($normalized === null) {
            throw new UnsupportedDataFormatException('', self::supported());
        }

        return self::ACCEPT_HEADERS[$normalized];
    }
}
