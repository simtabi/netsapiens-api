<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Request\Request;
use Simtabi\NetSapiens\Traits\HasHelperFunctions;

/**
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. Use Simtabi\NetSapiens\Resources\V2\Resource instead. Removal no earlier than 2.0.0.
 */
abstract class Resource
{

    use HasHelperFunctions;

    protected OAuth2  $oAuth2;
    protected Request $request;
    protected mixed   $cacher;
    protected array   $guzzleConfig = [];

    public function __construct(OAuth2 $oAuth2, ?callable $cacher = null, array $guzzleConfig = [])
    {
        $this->guzzleConfig = $guzzleConfig;
        $this->request      = new Request($oAuth2, $guzzleConfig);
        $this->oAuth2       = $oAuth2;
        $this->cacher       = $cacher;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getGuzzleConfig(): array
    {
        return $this->guzzleConfig;
    }

}
