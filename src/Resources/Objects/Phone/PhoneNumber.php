<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects\Phone;

use Psr\Http\Message\ResponseInterface;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class PhoneNumber  extends Resource
{

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

    public function read(array $params): array
    {

        $this->setEndpoint('?object=phonenumber&action=read', __FUNCTION__);

        $bodyParams = [
            'object'     => "phonenumber",
            'action'      => "read",
            'dest_domain' => $params["domain"],
            'to_user'     => $params["to-user"],
            'plan'        => $params["plan"],
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

        $this->setEndpoint('?object=phonenumber&action=create', __FUNCTION__);

        $bodyParams = [
            'object'      => "phonenumber",
            'action'      => "create",
            'dest_domain' => $params["domain"],
            'domain'      => $params["domain"],
            'to_user'     => $params["to-user"],
            'dialplan'    => $params["plan"],
            'matchrule'   => $params["match"],
            'responder'   => "sip:start@to-user",
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
