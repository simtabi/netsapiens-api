<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Http;

use Simtabi\NetSapiens\Exceptions\TransportException;

/**
 * Sends a request and returns whatever the server answered, error statuses included.
 *
 * Implementations throw only when no response arrived at all.
 */
interface Transport
{
    /**
     * @throws TransportException
     */
    public function send(Request $request): Response;
}
