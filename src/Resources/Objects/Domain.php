<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects;

use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class Domain  extends Resource
{


    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

    public function read(array $params): array
    {
        $this->setEndpoint('?object=domain&action=read', __FUNCTION__);

        $bodyParams = [
            'object' => "domain",
            'action' => "read",
        ];

        if ( isset($params["domain"]) ) {
            $bodyParams["domain"] =  $params["domain"];
        }

        $request    = $this->request->request(
            method:     'post',
            endpoint:   $this->getEndpoint(__FUNCTION__),
            parameters: [
                'body' => $bodyParams,
            ],
            headers:    [],
            verify: $this->guzzleConfig['verify'] ?? false,
            cacher: $this->cacher,
        );

        return [
            'status'   => $this->request->requestIsSuccessful(),
            'endpoint' => $this->getEndpoint(__FUNCTION__, true),
            'data'     => $request,
        ];
    }

    public function create(array $params): array
    {
        $this->setEndpoint('?object=domain&action=create', __FUNCTION__);

        $bodyParams = [
            'object' => "domain",
            'action' => "read",
        ];

        if ( isset($params["domain"]) ) {
            $bodyParams["domain"] =  $params["domain"];
        }

        $request    = $this->request->request(
            method:     'post',
            endpoint:   $this->getEndpoint(__FUNCTION__),
            parameters: [
                'body' => $bodyParams,
            ],
            headers:    [],
            verify: $this->guzzleConfig['verify'] ?? false,
            cacher: $this->cacher,
        );

        return [
            'status'   => $this->request->requestIsSuccessful(),
            'endpoint' => $this->getEndpoint(__FUNCTION__, true),
            'data'     => $request,
        ];
    }

}
