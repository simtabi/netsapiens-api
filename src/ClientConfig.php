<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens;

use InvalidArgumentException;

final class ClientConfig
{
    public const API_PATH = '/ns-api/v2';

    /**
     * @param string      $baseUri   e.g. https://pbx.example.com/ns-api/v2
     * @param string|null $logTag    sent as X-NetSapiens-Log so the calls show up in iNSight's on-demand log
     */
    public function __construct(
        public readonly string $baseUri,
        public readonly float $timeout = 30.0,
        public readonly string $userAgent = 'simtabi/netsapiens-api',
        public readonly ?string $logTag = null,
    ) {
    }

    /**
     * Accepts a bare host (pbx.example.com), a URL, or a URL that already ends in /ns-api/v2.
     */
    public static function forServer(string $server, float $timeout = 30.0, ?string $logTag = null): self
    {
        $server = trim($server);
        if ($server === '') {
            throw new InvalidArgumentException('A NetSapiens server is required, e.g. pbx.example.com');
        }

        if (!preg_match('#^https?://#i', $server)) {
            $server = 'https://' . $server;
        }

        $server = rtrim($server, '/');
        if (!str_ends_with(strtolower($server), self::API_PATH)) {
            $server .= self::API_PATH;
        }

        return new self($server, $timeout, logTag: $logTag);
    }
}
