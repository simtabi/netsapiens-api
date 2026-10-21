<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Http;

/**
 * One call to the NetSapiens v2 API, relative to the configured base URI.
 */
final class Request
{
    /**
     * @param array<string, scalar>           $query     null values are dropped by the caller
     * @param array<string, string>           $headers
     * @param array<array-key, mixed>|null    $body      sent as JSON when not null
     * @param list<array<string, mixed>>|null $multipart Guzzle-style parts: name, contents, filename
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $headers = [],
        public readonly ?array $body = null,
        public readonly ?array $multipart = null,
    ) {
    }

    public function withHeader(string $name, string $value): self
    {
        return new self($this->method, $this->path, $this->query, [$name => $value] + $this->headers, $this->body, $this->multipart);
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }

        return null;
    }
}
