<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects\Device;

use Psr\Http\Message\ResponseInterface;
use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Helpers\Helpers;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class Device  extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

    public function read(array $params): array
    {

        $this->setEndpoint('?object=device&action=read', __FUNCTION__);

        $bodyParams = [
            'object'       => "device",
            'action'       => "read",
            'owner_domain' =>  $params["domain"]
        ];

        if ( isset($params["user"]) ) {
            $bodyParams["owner"] =  $params["user"];
        }

        $request = $this->request->request(
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

        $this->setEndpoint('?object=device&action=create', __FUNCTION__);

        $bodyParams = [
            'object'       => "device",
            'action'       => "create",
            'aor'          =>  "sip:" . $params["user"] . "@" . $params["domain"],
            'owner'        =>  $params["user"],
            'domain'       =>  $params["domain"],
            'owner_domain' =>  $params["domain"],
        ];

        if ( isset($params["contact"]) ) {
            $bodyParams["aor"] =  "sip:" . $params["contact"] . "@" . $params["domain"];
        }

        if ( isset($params["auth-key"]) ) {
            $bodyParams["auth_key"] =  $params["auth-key"];
        }

        $request = $this->request->request(
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

    public function setDevicePassword(array $params): array
    {

        /** @todo fix api endpoint */

        $this->setEndpoint('?object=queuedcall&action=create', __FUNCTION__);

        $bodyParams = [
            'object'       => "device",
            'action'       => "update",
            'aor'          =>  "sip:" . $params["contact"] . "@" . $params["domain"],
            'auth_key'     =>  $params["auth-key"],
            'owner'        =>  $params["user"],
            'owner_domain' =>  $params["domain"],
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
