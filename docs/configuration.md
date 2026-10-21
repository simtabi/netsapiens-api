# Configuration

`ClientConfig` holds the server, the timeout, the user agent and the iNSight log tag.

## Server and timeout

```php
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\ClientConfig;

$config = ClientConfig::forServer('pbx.example.com', timeout: 10.0);

$ns = Client::withApiKey($config, getenv('NETSAPIENS_API_KEY'));
```

| Input | Base URI |
|---|---|
| `pbx.example.com` | `https://pbx.example.com/ns-api/v2` |
| `https://pbx.example.com/` | `https://pbx.example.com/ns-api/v2` |
| `http://10.0.0.5:8080` | `http://10.0.0.5:8080/ns-api/v2` |
| `https://pbx.example.com/ns-api/v2` | unchanged |

The timeout is in seconds and defaults to 30.

## iNSight on-demand logging

NetSapiens logs any request that carries an `X-NetSapiens-Log` header to iNSight's *On-demand API and Portal Logs* dashboard, where you can search for the tag. Set a tag and the client sends it on every call:

```php
$config = ClientConfig::forServer('pbx.example.com', logTag: 'ticket-4821');
```

## TLS

Certificates are always verified. To trust a private CA, give `GuzzleTransport` a Guzzle client that points at the bundle:

```php
$transport = new GuzzleTransport($config->baseUri, new Guzzle(['verify' => '/etc/ssl/internal-ca.pem']));
```

Turning verification off (`'verify' => false`) exposes your API key or token to anyone on the network path. Do it only against a local test server.

## Custom transport

Every factory takes an optional `Transport`. Pass a `GuzzleTransport` with your own Guzzle client to add a proxy, retries or middleware:

```php
use GuzzleHttp\Client as Guzzle;
use Simtabi\NetSapiens\Http\GuzzleTransport;

$transport = new GuzzleTransport($config->baseUri, new Guzzle(['proxy' => 'http://proxy.internal:3128']));

$ns = Client::withApiKey($config, getenv('NETSAPIENS_API_KEY'), $transport);
```

The transport must return error statuses rather than throw them. `GuzzleTransport` sets `http_errors` to `false` for that reason.

---

[← Docs index](../README.md#documentation)
