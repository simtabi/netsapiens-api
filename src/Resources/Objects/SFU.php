<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

/**
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. docs.ns-api.com documents no v2 equivalent; the class has no methods. Removal no earlier than 2.0.0.
 */
class SFU extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

}