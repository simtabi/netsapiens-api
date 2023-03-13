<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Objects\Call;

use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Helpers\Helpers;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use Simtabi\NetSapiens\Resources\Resource;

class CallQueue extends Resource
{

    protected string|null   $queue = null;
    protected string|null   $uid   = null;

    public function __construct(OAuth2 $oAuth2, callable|null $cacher = null, array $guzzleConfig = [])
    {
        parent::__construct($oAuth2, $cacher, $guzzleConfig);
    }

    /**
     * @param string|null $queue
     *
     * @return static
     */
    public function setQueue(?string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * @param string|null $uid
     *
     * @return static
     */
    public function setUid(?string $uid): static
    {
        $this->uid = $uid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUid(): ?string
    {
        return $this->uid;
    }


    /**
     * @throws NetSapiensException
     */
    public function create(string|int $phoneNumber): array
    {

        if (!Helpers::isValidPhoneNumber($phoneNumber)) {
            throw new NetSapiensException('Not a valid phone number. Please check and try again.');
        }

        $this->setEndpoint('?object=queuedcall&action=create', __FUNCTION__);

        $request    = $this->request->request(
            method:     'post',
            endpoint:   $this->getEndpoint(__FUNCTION__),
            parameters: [
                'json' => [
                    'destination' => $phoneNumber,
                    'queue'       => $this->getQueue(),
                    'uid'         => $this->getUid(),
                ],
            ],
            headers:    [
                'Content-Type'  => 'application/json',
            ],
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