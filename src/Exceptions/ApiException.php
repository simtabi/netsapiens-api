<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Exceptions;

use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Response;

/**
 * The API answered with an error status. The message is the API's ErrorResponse message when it
 * sent one.
 */
class ApiException extends NetSapiensException
{
    final public function __construct(
        string $message,
        public readonly int $status,
        public readonly Request $request,
        public readonly Response $response,
    ) {
        parent::__construct($message, $status);
    }

    public static function fromResponse(Request $request, Response $response): self
    {
        $decoded = $response->decoded();
        $detail  = is_array($decoded) && isset($decoded['message']) && is_string($decoded['message'])
            ? $decoded['message']
            : trim($response->body);

        $message = sprintf('%s %s returned %d%s', $request->method, $request->path, $response->status, $detail === '' ? '' : ': ' . $detail);

        $class = match (true) {
            $response->status === 400 => ValidationException::class,
            $response->status === 401 => AuthenticationException::class,
            $response->status === 403 => AuthorizationException::class,
            $response->status === 404 => NotFoundException::class,
            $response->status === 409 => ConflictException::class,
            $response->status >= 500  => ServerException::class,
            default                   => self::class,
        };

        return new $class($message, $response->status, $request, $response);
    }
}
