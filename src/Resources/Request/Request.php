<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Request;

use DOMDocument;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Matomo\Cache\Backend\Factory\BackendNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidTypeException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Simtabi\Laranail\Nails\General\Traits\HasErrorStorage;
use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Helpers\Helpers;
use Simtabi\NetSapiens\Resources\Auth\OAuth2;
use TypeError;

class Request
{

    use HasErrorStorage;

    private OAuth2             $oAuth2;
    private Client             $guzzle;
    private array              $guzzleConfig;

    private ?ResponseInterface $response;
    private array              $responseStatus = [];

    public function __construct(OAuth2 $oAuth2, array $guzzleConfig = [])
    {
        $this->oAuth2       = $oAuth2;
        $this->guzzleConfig = $guzzleConfig;
        $this->guzzle       = new Client(array_merge([
                'base_uri' => $this->oAuth2->getBaseUrl()
            ], $guzzleConfig)
        );
    }

    /**
     * @param Client $httpClient
     *
     * @return static
     */
    private function setHttpClient(Client $httpClient): static
    {
        $this->guzzle = $httpClient;
        return $this;
    }

    /**
     * @return Client
     */
    public function getGuzzle(): Client
    {
        return $this->guzzle;
    }

    /**
     * @param array $guzzleConfig
     *
     * @return static
     */
    public function setGuzzleConfig(array $guzzleConfig): static
    {
        $this->guzzleConfig = $guzzleConfig;
        return $this;
    }

    /**
     * @return array
     */
    public function getGuzzleConfig(): array
    {
        return $this->guzzleConfig;
    }

    /**
     * @param ?ResponseInterface $response
     *
     * @return static
     */
    public function setResponse(?ResponseInterface $response): static
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param array|string $responseStatus
     *
     * @return static
     */
    public function setResponseStatus(array|string $responseStatus): static
    {
        $this->responseStatus = $responseStatus;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponseStatus(): array
    {
        return $this->responseStatus;
    }


    public function request(string $method, string $endpoint, array $parameters, array $headers = [], bool $verify = false, callable|null $cacher = null): bool|string|StreamInterface
    {
        try {

            // Ensure Request Method is being provided
            if (empty($method)) {
                throw new NetsapiensException(Helpers::ERROR_METHOD);
            }

            // Authenticate
            $this->oAuth2->generateAccessToken($cacher);

            // Request data
            $parameters['verify']  = $verify;
            $parameters['headers'] = array_merge($headers, [
                'Authorization' => 'Bearer ' . $this->oAuth2->getAccessToken(),
            ]);

            // Send Request
            $response = $this->guzzle->request(strtoupper($method), $endpoint, $parameters);

            // Analyze response status codes, and message
            $this->analyzeResponseStatus($response);

            // Set response for later processing
            $this->setResponse($response);

            if (!empty($response->getBody())) {
                return  $response->getBody();
            } else {
                return $response->getBody()->getContents();
            }

        } catch (GuzzleException|NetsapiensException $exception) {
            $this->setErrors($exception->getMessage());
        }

        return false;
    }

    protected function analyzeResponseStatus(ResponseInterface $response): void
    {
        try {

            $prettifyResponse = function ($body): mixed
            {

                if ($body instanceof Stream) {
                    $body = $body->getContents();
                }

                if (!empty($body)) {
                    $domDocument                     = new DOMDocument();
                    $domDocument->preserveWhiteSpace = false;
                    $domDocument->formatOutput       = true;

                    if ( ! @$domDocument->loadXML($body) ) {
                        return $body; // if it's not valid XML, then just return the $body string unprocessed
                    }

                    return $domDocument->saveXML();
                }

                return null;
            };

            $statusCode = $response->getStatusCode();
            $body       = $response->getBody();

            if ( $statusCode != "200" || "$body" == "" ) {
                $this->setResponseStatus(['status' => [
                    'code'   => $statusCode,
                    'reason' => $response->getReasonPhrase(),
                    'phrase' => $statusCode . " " . $response->getReasonPhrase(),
                ]]);
            } else {
                $this->setResponseStatus([
                    'pretty_response' => $prettifyResponse($body),
                ]);
            }

        } catch (NetSapiensException|Exception $exception) {
            $this->setResponseStatus([
                'exception_request' => $exception->getRequest(),
            ]);

            if ($exception->hasResponse()) {
                $this->setResponseStatus([
                    'exception_response' => $exception->getResponse(),
                ]);
            }
        } catch (TypeError $exception) {
            $this->setErrors($exception->getMessage());
        }
    }

    public function requestIsSuccessful(): bool
    {
        if (!empty($this->response)) {
            if ($this->response->getStatusCode() >= 200 && $this->response->getStatusCode() <= 299) {
                return true;
            }
        }

        return false;
    }

}
