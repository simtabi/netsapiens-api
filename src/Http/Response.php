<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Http;

use JsonException;

final class Response
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        public readonly int $status,
        public readonly string $body = '',
        public readonly array $headers = [],
    ) {
    }

    public static function json(int $status, mixed $data): self
    {
        return new self($status, json_encode($data, JSON_THROW_ON_ERROR), ['Content-Type' => ['application/json']]);
    }

    public function isSuccessful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $values) {
            if (strcasecmp($key, $name) === 0) {
                return $values[0] ?? null;
            }
        }

        return null;
    }

    /**
     * The decoded JSON body, or null when the body is empty or is not JSON.
     */
    public function decoded(): mixed
    {
        if (trim($this->body) === '') {
            return null;
        }

        try {
            return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }
}
