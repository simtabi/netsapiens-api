<?php declare(strict_types=1);

use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\NetSapiens;

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
