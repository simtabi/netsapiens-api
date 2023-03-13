<?php declare(strict_types=1);


namespace Simtabi\NetSapiens\Resources\Objects;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class AnswerRule extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

}