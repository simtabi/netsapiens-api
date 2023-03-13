<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects\Sites;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class Sites extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $guzzleConfig);

        $this->oAuth2 = $oAuth2;
        $this->cacher           = $cacher;
    }


}