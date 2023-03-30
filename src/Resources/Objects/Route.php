<?php declare(strict_types=1);

namespace USIPCOM\NetSapiens\Resources\Objects;

use USIPCOM\NetSapiens\Resources\Auth\OAuth2;
use USIPCOM\NetSapiens\Resources\Resource;

class Route extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

}