<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

use Simtabi\NetSapiens\Http\Request;

/**
 * A fixed bearer: an API key (nss_…), or a token obtained elsewhere.
 */
final class BearerTokenAuthenticator implements Authenticator
{
    public function __construct(private readonly string $token)
    {
    }

    public function authorize(Request $request): Request
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->token);
    }

    public function refresh(): bool
    {
        return false;
    }
}
