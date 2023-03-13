<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Resources\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Simtabi\Laranail\Nails\General\Traits\HasErrorStorage;
use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Helpers\Helpers;

class OAuth2
{
    use HasErrorStorage;

    private const              AUTH_CACHE_KEY  = 'netsapiens_refresh_token';

    private string|null        $baseUrl        = null;

    private string|null        $clientId       = null;
    private string|null        $clientSecret   = null;
    private string|null        $username       = null;
    private string|null        $password       = null;

    private string|null        $user           = null;
    private string|null        $territory      = null;
    private string|null        $domain         = null;
    private string|null        $department     = null;
    private string|null        $login          = null;
    private string|null        $scope          = null;
    private string|null        $uid            = null;

    private string|null        $userEmail      = null;
    private string|null        $displayName    = null;
    private string|null        $accessToken    = null;
    private string|int|null    $expiresIn      = null;
    private string|null        $tokenType      = null;
    private string|null        $refreshToken   = null;
    private string|null        $apiVersion     = null;

    /**
     */
    public function __construct(string|null $baseUrl)
    {
        if (!empty($baseUrl)) {
            $this->setBaseUrl($baseUrl);
        }
    }

    /**
     * @param string|null $baseUrl
     *
     * @return static
     */
    public function setBaseUrl(?string $baseUrl): static
    {
        $this->baseUrl = !empty($baseUrl) ? rtrim($baseUrl, '/') . '/' : null;;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    /**
     * @param string|null $clientId
     *
     * @return static
     */
    public function setClientId(?string $clientId): static
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    /**
     * @param string|null $clientSecret
     *
     * @return static
     */
    public function setClientSecret(?string $clientSecret): static
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    /**
     * @param string|null $username
     *
     * @return static
     */
    public function setUsername(?string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string|null $password
     *
     * @return static
     */
    public function setPassword(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $user
     *
     * @return static
     */
    public function setUser(?string $user): static
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string|null $territory
     *
     * @return static
     */
    public function setTerritory(?string $territory): static
    {
        $this->territory = $territory;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTerritory(): ?string
    {
        return $this->territory;
    }

    /**
     * @param string|null $domain
     *
     * @return static
     */
    public function setDomain(?string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string|null $department
     *
     * @return static
     */
    public function setDepartment(?string $department): static
    {
        $this->department = $department;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDepartment(): ?string
    {
        return $this->department;
    }

    /**
     * @param string|null $login
     *
     * @return static
     */
    public function setLogin(?string $login): static
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string|null $scope
     *
     * @return static
     */
    public function setScope(?string $scope): static
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
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
     * @param string|null $userEmail
     *
     * @return static
     */
    public function setUserEmail(?string $userEmail): static
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    /**
     * @param string|null $displayName
     *
     * @return static
     */
    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $accessToken
     *
     * @return static
     */
    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param string|null $expiresIn
     *
     * @return static
     */
    public function setExpiresIn(?string $expiresIn): static
    {
        $this->expiresIn = $expiresIn;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpiresIn(): ?string
    {
        return $this->expiresIn;
    }

    /**
     * @param string|null $tokenType
     *
     * @return static
     */
    public function setTokenType(?string $tokenType): static
    {
        $this->tokenType = $tokenType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    /**
     * @param string|null $refreshToken
     *
     * @return static
     */
    public function setRefreshToken(?string $refreshToken): static
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string|null $apiVersion
     *
     * @return static
     */
    public function setApiVersion(?string $apiVersion): static
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiVersion(): ?string
    {
        return $this->apiVersion;
    }

    protected function cacher(callable $cacher, mixed ...$args)
    {
        return call_user_func($cacher, $args);
    }

    /**
     * @throws NetSapiensException
     */
    private function request(string $grantType, callable $cacher, string|null $refreshToken = null)
    {

        if (empty($this->clientId)) {
            $this->setErrors(Helpers::ERROR_CLIENT_ID);
        }

        if (empty($this->clientSecret)) {
            $this->setErrors(Helpers::ERROR_CLIENT_SECRET);
        }

        if (empty($this->username)) {
            $this->setErrors(Helpers::ERROR_USERNAME);
        }

        if (empty($this->password)) {
            $this->setErrors(Helpers::ERROR_PASSWORD);
        }

        if (empty($this->baseUrl)) {
            $this->setErrors(Helpers::ERROR_BASE_URL);
        }

        if (empty($grantType)) {
            $this->setErrors(Helpers::ERROR_GRANT_TYPE);
        }

        if (empty($refreshToken) && $grantType === 'refresh_token') {
            $this->setErrors(Helpers::ERROR_REFRESH_TOKEN);
        }

        if (!empty($this->errors)) {
            throw new NetsapiensException(Helpers::ENCOUNTERED_ERRORS);
        }

        if (!is_callable($cacher) || empty($cacher)) {
            throw new NetSapiensException('You must provide a caching function');
        }

        try {

            // Default credentials
            $parameters['client_id']     = $this->clientId;
            $parameters['client_secret'] = $this->clientSecret;
            $parameters['grant_type']    = $grantType;

            // Define credentials based on grant type
            if (pheg()->str()->compare()->string($grantType, 'refresh_token')) {
                $parameters['refresh_token'] = $this->password;
            } else {
                $parameters['username']      = $this->username;
                $parameters['password']      = $this->password;
            }

            // Authenticate
            $response = (new Client([
                'base_uri' => $this->baseUrl,
            ]))->post('ns-api/oauth2/token', [
                'headers' => ['Content-Type' => 'application/json',],
                'body'    => json_encode($parameters),
            ]);

            // Process response
            $data = json_decode($response->getBody()->getContents(), false);

            if (isset($data->access_token)) {

                // Push to Cache
                $data = $this->cacher($cacher, $data, self::AUTH_CACHE_KEY);

                // Push to class variables after caching
                $this->username     = $data->username      ?? null;
                $this->user         = $data->user          ?? null;
                $this->territory    = $data->territory     ?? null;
                $this->domain       = $data->domain        ?? null;
                $this->department   = $data->department    ?? null;
                $this->uid          = $data->uid           ?? null;
                $this->login        = $data->login         ?? null;
                $this->scope        = $data->scope         ?? null;
                $this->userEmail    = $data->user_email    ?? null;
                $this->displayName  = $data->displayName   ?? null;
                $this->accessToken  = $data->access_token  ?? null;
                $this->expiresIn    = $data->expires_in    ?? null;
                $this->tokenType    = $data->token_type    ?? null;
                $this->refreshToken = $data->refresh_token ?? null;
                $this->clientId     = $data->client_id     ?? null;
                $this->apiVersion   = $data->apiversion    ?? null;

                return true;
            } else {
                throw new NetsapiensException(Helpers::AUTH_CONNECTION_ERROR);
            }

        } catch (GuzzleException|NetsapiensException $exception) {
            $this->setErrors($exception->getMessage());
        }

        return false;
    }

    /**
     * @param callable $cacher
     *
     * @return bool
     * @throws NetSapiensException
     */
    public function generateAccessToken(callable $cacher): bool
    {
        return $this->request('password', $cacher);
    }

    /**
     * @param string   $refreshToken
     * @param callable $cacher
     *
     * @return bool
     * @throws NetSapiensException
     */
    public function generateRefreshToken(string $refreshToken, callable $cacher): bool
    {
        return $this->request('refresh_token', $cacher, $refreshToken);
    }

}
