<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects;

use Psr\Http\Message\ResponseInterface;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class Subscriber  extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

    public function count(array $params): array
    {

        $this->setEndpoint('?object=subscriber&action=count', __FUNCTION__);

        $bodyParams = [
            'object' => "subscriber",
            'action' => "count",
            'domain' => $params["domain"],
        ];

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

    public function read(array $params): array
    {

        $this->setEndpoint('?object=subscriber&action=read', __FUNCTION__);

        $bodyParams = [
            'object' => "subscriber",
            'action' => "read",
            'domain' => $params["domain"],
            'limit'  => $params["limit"],
        ];

        if ( isset($params["user"]) ) {
            $bodyParams["user"] =  $params["user"];
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

    public function update(array $params): array
    {

        $this->setEndpoint('?object=subscriber&action=update', __FUNCTION__);

        $bodyParams = [
            $params["key"] => $params["value"],
            'object'       => "subscriber",
            'action'       => "update",
            'domain'       => $params["domain"],
            'user'         => $params["user"],
        ];

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

        $this->setEndpoint('?object=subscriber&action=create', __FUNCTION__);

        $bodyParams = [
            'object'   => "subscriber",
            'action'   => "create",
            'domain'   => $params["domain"],
            'user'     => $params["user"],
            'username' => $params["username"],
        ];

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
