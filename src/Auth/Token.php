<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

final class Token
{
    /**
     * @param int|null $expiresAt unix time; null when the server did not say
     */
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken = null,
        public readonly ?int $expiresAt = null,
    ) {
    }

    public function isExpired(int $now, int $leeway = 30): bool
    {
        return $this->expiresAt !== null && $this->expiresAt - $leeway <= $now;
    }

    /**
     * @return array{access: string, refresh: string|null, expires: int|null}
     */
    public function toArray(): array
    {
        return ['access' => $this->accessToken, 'refresh' => $this->refreshToken, 'expires' => $this->expiresAt];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ?self
    {
        if (!isset($data['access']) || !is_string($data['access'])) {
            return null;
        }

        return new self(
            $data['access'],
            isset($data['refresh']) && is_string($data['refresh']) ? $data['refresh'] : null,
            isset($data['expires']) && is_int($data['expires']) ? $data['expires'] : null,
        );
    }
}
