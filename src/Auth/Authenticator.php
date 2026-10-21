<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Auth;

use Simtabi\NetSapiens\Http\Request;

interface Authenticator
{
    /**
     * Return the request with its Authorization header set.
     */
    public function authorize(Request $request): Request;

    /**
     * Called once after a 401. Return true when fresh credentials were obtained and the request
     * is worth sending again.
     */
    public function refresh(): bool;
}
