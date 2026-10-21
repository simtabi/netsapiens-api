<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Simtabi\NetSapiens\Exceptions\TransportException;

final class GuzzleTransport implements Transport
{
    private readonly ClientInterface $guzzle;

    /**
     * @param string $baseUri e.g. https://pbx.example.com/ns-api/v2/
     */
    public function __construct(
        private readonly string $baseUri,
        ?ClientInterface $guzzle = null,
        float $timeout = 30.0,
    ) {
        $this->guzzle = $guzzle ?? new GuzzleClient(['timeout' => $timeout]);
    }

    public function send(Request $request): Response
    {
        $options = ['http_errors' => false, 'headers' => $request->headers];

        if ($request->query !== []) {
            $options['query'] = $request->query;
        }

        if ($request->multipart !== null) {
            $options['multipart'] = $request->multipart;
        } elseif ($request->body !== null) {
            // An empty array must still reach the API as an object.
            $options['body'] = $request->body === [] ? '{}' : json_encode($request->body, JSON_THROW_ON_ERROR);
            $options['headers'] += ['Content-Type' => 'application/json'];
        }

        try {
            $response = $this->guzzle->request($request->method, $this->url($request->path), $options);
        } catch (GuzzleException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return new Response($response->getStatusCode(), (string) $response->getBody(), $response->getHeaders());
    }

    private function url(string $path): string
    {
        return rtrim($this->baseUri, '/') . '/' . ltrim($path, '/');
    }
}
