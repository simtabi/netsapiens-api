<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Exceptions;

/**
 * 401 Unauthorized: the credentials were missing, wrong or expired.
 */
final class AuthenticationException extends ApiException
{
}
