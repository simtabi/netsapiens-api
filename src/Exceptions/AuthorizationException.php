<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Exceptions;

/**
 * 403 Forbidden: the credentials are valid but lack the scope for this call.
 */
final class AuthorizationException extends ApiException
{
}
