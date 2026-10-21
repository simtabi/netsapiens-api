<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects\Dial;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

/**
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. Use Simtabi\NetSapiens\Client::dialRules() instead. Removal no earlier than 2.0.0.
 */
class DialPlan extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

}