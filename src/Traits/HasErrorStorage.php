<?php

namespace Simtabi\NetSapiens\Traits;

/**
 * Collects validation and request errors on the object that uses it.
 *
 * This package used `Simtabi\Laranail\Nails\General\Traits\HasErrorStorage` without requiring the
 * package that provided it, which no longer exists, so `OAuth2` and `Request` could not be loaded.
 * This keeps the same public surface (`getErrors`, `hasErrors`, `clearErrors`, `addError`,
 * `getErrorCount`, `getFirstError`, protected `setErrors`) without a framework dependency.
 *
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. v2 throws Simtabi\NetSapiens\Exceptions\ApiException and its subclasses instead. Removal no earlier than 2.0.0.
 */
trait HasErrorStorage
{
    /** @var array<int|string, mixed> */
    private array $errorStorage = [];

    /**
     * @return array<int|string, mixed>
     */
    public function getErrors(?string $key = null): array
    {
        if ($key === null) {
            return $this->errorStorage;
        }

        return isset($this->errorStorage[$key]) ? (array) $this->errorStorage[$key] : [];
    }

    public function hasErrors(): bool
    {
        return $this->errorStorage !== [];
    }

    public function clearErrors(): static
    {
        $this->errorStorage = [];

        return $this;
    }

    public function addError(string $key, string $message): static
    {
        $this->errorStorage[$key] = $message;

        return $this;
    }

    public function getErrorCount(): int
    {
        return count($this->errorStorage);
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errorStorage as $error) {
            return is_scalar($error) ? (string) $error : null;
        }

        return null;
    }

    /**
     * Append one error or merge a list of them.
     *
     * @param array<int|string, mixed>|string $errors
     */
    protected function setErrors(array|string $errors): static
    {
        if (is_array($errors)) {
            $this->errorStorage = array_merge($this->errorStorage, $errors);
        } else {
            $this->errorStorage[] = $errors;
        }

        return $this;
    }
}
