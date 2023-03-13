<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Request\Request;
use Simtabi\NetSapiens\Traits\HasHelperFunctions;

abstract class Resource
{

    use HasHelperFunctions;

    protected OAuth2  $oAuth2;
    protected Request $request;
    protected mixed   $cacher;
    protected array   $guzzleConfig = [];

    public function __construct(OAuth2 $oAuth2, callable $cacher, array $guzzleConfig = [])
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
