<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Helpers;

use Closure;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Exceptions\PhpfastcacheDriverCheckException;
use Phpfastcache\Exceptions\PhpfastcacheDriverException;
use Phpfastcache\Exceptions\PhpfastcacheDriverNotFoundException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidArgumentException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;
use Phpfastcache\Exceptions\PhpfastcacheInvalidTypeException;
use Phpfastcache\Exceptions\PhpfastcacheLogicException;
use Psr\Cache\InvalidArgumentException;
use Simtabi\NetSapiens\Exceptions\NetSapiensException;

class Helpers
{

    public const      CACHE_LIFETIME_IN_SECONDS  = 7200; // 2Hrs = 7200

    public const      ERROR_CLIENT_ID       = "You must provide a Client ID";

    public const      ERROR_CLIENT_SECRET   = "You must provide a Client Secret";

    public const      ERROR_USERNAME        = "You must provide a Username";

    public const      ERROR_PASSWORD        = "You must provide a Password";

    public const      ERROR_BASE_URL        = "You must provide a Base API URL";

    public const      ERROR_GRANT_TYPE      = "You must provide a valid Grant Type";

    public const      ERROR_REFRESH_TOKEN   = "You must provide a valid Refresh Token";

    public const      ERROR_METHOD          = "You must provide a Method Type for your request";

    public const      AUTH_CONNECTION_ERROR = "NetSapiens client failed to sign in.";
    public const      EMPTY_CACHE_ERROR     = "You must provide a cache path";

    public const      ENCOUNTERED_ERRORS    = "There are error in your request. Please check the errors and try again!";


    /**
     * @param callable $items
     * @param string   $cacheId
     * @param string   $cachePath
     * @param int      $lifeTimeInSeconds
     * @param bool     $resetCache
     *
     * @return mixed
     * @throws NetSapiensException
     */
    public static function cacher(mixed $items, string $cacheId, string $cachePath, int $lifeTimeInSeconds = Helpers::CACHE_LIFETIME_IN_SECONDS, bool $resetCache = false): mixed
    {
        try {
            // Ensure cache path is provided
            if (empty($cachePath)) {
                throw new NetSapiensException(Helpers::EMPTY_CACHE_ERROR);
            }

            // Setup File Path on your config files
            // Please note that as of the V6.1 the "path" config
            // can also be used for Unix sockets (Redis, Memcache, etc)
            CacheManager::setDefaultConfig(new ConfigurationOption([
                'path' => $cachePath, // or in windows "C:/tmp/"
            ]));

            // In your class, function, you can call the Cache
            $cacheInstance = CacheManager::getInstance('files');
            $fetchCache    = $cacheInstance->getItem($cacheId);

            // Reset cache if we are required to
            if ($resetCache) {
                if ($cacheInstance->hasItem($cacheId)) {
                    $cacheInstance->deleteItem($cacheId);
                }
            }

            // Push to cache
            if (!$fetchCache->isHit()) {
                $fetchCache->set($items)->expiresAfter($lifeTimeInSeconds);//in seconds, also accepts Datetime
                $cacheInstance->save($fetchCache); // Save the cache item just like you do with doctrine and entities
                return $items;
            } else {
                return $fetchCache->get();
            }

        } catch (
        PhpfastcacheDriverCheckException |
        PhpfastcacheDriverException |
        PhpfastcacheDriverNotFoundException |
        PhpfastcacheInvalidArgumentException |
        PhpfastcacheInvalidConfigurationException |
        PhpfastcacheInvalidTypeException |
        PhpfastcacheLogicException |
        InvalidArgumentException $exception) {
            throw new NetSapiensException($exception->getMessage());
        }

    }

    public static function buildEndpointUrl(string $endpoint, string|null $baseUrl = null, bool $appendBaseUrl = false): string
    {
        $baseUrl = $appendBaseUrl && !empty($baseUrl) ? rtrim($baseUrl, '/') : '';

        return $baseUrl . '/ns-api/' . ltrim($endpoint, '/');
    }

    public static function isValidPhoneNumber(string|int $phoneNumber): bool
    {
        return true;
    }

    public static function isCallableFunc($f): bool
    {
        return is_callable($f) && ( !is_object($f) || $f instanceof Closure);
    }
}
