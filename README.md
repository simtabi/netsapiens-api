# simtabi/netsapiens-api

[![Tests](https://github.com/simtabi/netsapiens-api/actions/workflows/main.yml/badge.svg)](https://github.com/simtabi/netsapiens-api/actions/workflows/main.yml)
[![License MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)

Two badges, not four: the package is not on Packagist yet, so there is no registry version to show, and CI runs tests but no static analysis.

> PHP SDK for the NetSapiens API v2: all 414 operations on docs.ns-api.com, typed, with API key, OAuth and JWT authentication.

Requires PHP 8.1 to 8.4 and Guzzle 7.

## Install

The package is not on Packagist yet, so add the repository first:

```bash
composer config repositories.netsapiens-api vcs https://github.com/simtabi/netsapiens-api
```

```bash
composer require simtabi/netsapiens-api
```

## Quick start guide and usage

### Getting started

You need a NetSapiens server host and one credential: an API key (`nss_…`) from the Admin UI, or an OAuth client ID and secret plus a user login. Nothing needs publishing or migrating. The v2 base path `/ns-api/v2` is added to the host for you.

### Usage

```php
use Simtabi\NetSapiens\Client;

$ns = Client::withApiKey('pbx.example.com', getenv('NETSAPIENS_API_KEY'));

$domains = $ns->domains()->getDomains(limit: 100);   // list<Data\Domain>
$total   = $ns->users()->countUsers('example.com')->total;
```

```php
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Client;

$ns = Client::withAccessToken('pbx.example.com', new PasswordCredentials(
    clientId: 'acme-portal',
    clientSecret: getenv('NETSAPIENS_CLIENT_SECRET'),
    username: '1001@example.com',
    password: getenv('NETSAPIENS_PASSWORD'),
));

$ns->users()->createUser('example.com', ['user' => '1002', 'name-first-name' => 'Ada']);
```

```php
use Simtabi\NetSapiens\Exceptions\NotFoundException;

try {
    $ns->domains()->getDomain('missing.example.com');
} catch (NotFoundException $e) {
    echo $e->getMessage(); // GET /domains/missing.example.com returned 404: …
}
```

The full walkthrough is in [docs/getting-started.md](docs/getting-started.md); everything else is under [Documentation](#documentation).

## <a name="documentation"></a>Documentation

Hosted at <https://opensource.simtabi.com/documentation/simtabi/netsapiens-api/>.

### Guides

- [Installation](docs/installation.md) — requirements and the VCS repository entry
- [Getting started](docs/getting-started.md) — connect, read, write, handle errors
- [Configuration](docs/configuration.md) — server, timeout, iNSight log tag, custom transports
- [Architecture](docs/architecture.md) — how the client is put together, and why it is generated
- [Release](docs/release.md) — versioning and the deprecation schedule

### Reference

- [Authentication](docs/tools/authentication.md) — API keys, access tokens, JWT, MFA, token stores
- [Resources](docs/tools/resources.md) — the 78 accessors and how methods are named
- [Models](docs/tools/models.md) — the 16 typed response models
- [Errors](docs/tools/errors.md) — the exception for each status
- [Pagination](docs/tools/pagination.md) — start, limit and `Paginator::each()`
- [Testing](docs/tools/testing.md) — `FakeTransport`, for this package and yours
- [Legacy v1 client](docs/tools/legacy-v1.md) — the deprecated `NetSapiens` class

### Recipes

- [Walk every user in a domain](docs/recipes/walk-every-user.md)
- [Share tokens across workers](docs/recipes/share-tokens-across-workers.md)
- [Fake the API in your tests](docs/recipes/fake-the-api-in-tests.md)
- [Call an endpoint the SDK does not cover](docs/recipes/call-an-undocumented-endpoint.md)
- [Migrate from the v1 client](docs/recipes/migrate-from-v1.md)

## Contributing and security

See [CONTRIBUTING.md](CONTRIBUTING.md). Report vulnerabilities to `security@simtabi.com`, not the issue tracker. Written by [Imani Manyara](https://github.com/imanimanyara) and [Easter Mukora](https://github.com/eastermukora), with [all contributors](https://github.com/simtabi/netsapiens-api/graphs/contributors).

## License

MIT. See [LICENSE.md](LICENSE.md).
