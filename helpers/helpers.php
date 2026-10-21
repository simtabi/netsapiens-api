<?php declare(strict_types=1);

use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\NetSapiens;

/**
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. Use Simtabi\NetSapiens\Client::withAccessToken() instead. Removal no earlier than 2.0.0.
 */
if(!function_exists('netSapiensClient'))
{
    function netSapiensClient(string $clientId, string $clientSecret, string $username, string $password, string $baseUrl, callable|null $cacher = null, array $guzzleConfig = []): NetSapiens|string
    {
        try {
            return NetSapiens::getInstance(
                clientId         : $clientId,
                clientSecret     : $clientSecret,
                username         : $username,
                password         : $password,
                baseUrl          : $baseUrl,
                cacher           : $cacher,
                guzzleConfig     : $guzzleConfig
            );
        } catch (NetSapiensException $exception) {
            return $exception->getMessage();
        }
    }
}
