<?php declare(strict_types=1);

namespace Simtabi\NetSapiens\Traits;

use Simtabi\NetSapiens\Helpers\Helpers;

/**
 * @deprecated 1.1.0 The v1 API (ns-api/?object=…&action=…) is no longer documented on
 *             docs.ns-api.com. No replacement: v2 resources build their paths from the documented templates. Removal no earlier than 2.0.0.
 */
trait HasHelperFunctions
{

    protected array|null $endpoint = [];

    /**
     * @param string|null $endpoint
     * @param string      $key
     *
     * @return static
     */
    public function setEndpoint(?string $endpoint, string $key): static
    {
        $this->endpoint[$key] = $endpoint;

        return $this;
    }

    public function getEndpoint(string $key, bool $appendBaseUrl = false): string
    {
        return Helpers::buildEndpointUrl($this->endpoint[$key], $this->oAuth2->getBaseUrl(), $appendBaseUrl);
    }

    public function getEndpoints(bool $group = false, bool $appendBaseUrl = false): array
    {
        if (!empty($this->endpoint)) {
            foreach ($this->endpoint as $item => $value) {
                $endpointUrl = Helpers::buildEndpointUrl(
                    $value,
                    $this->oAuth2->getBaseUrl(),
                    $appendBaseUrl
                );

                if ($group) {
                    $data[$item] = $endpointUrl;
                } else {
                    $data[]      = $endpointUrl;
                }
            }
        }

        return $data ?? [];
    }

}
