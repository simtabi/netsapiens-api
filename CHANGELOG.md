# Changelog

All notable changes to `simtabi/netsapiens-api` are documented in this file.

## [Unreleased]

## [1.1.0] - 2026-10-21

### Added

- **A client for the NetSapiens API v2**, `Simtabi\NetSapiens\Client`, covering all 414 operations
  documented on docs.ns-api.com as of 2026-10-21, through 78 resource accessors (`$ns->domains()`,
  `$ns->users()`, …). Methods are generated from the documentation by `.dev/generate.php`, take
  typed path and query arguments, and link their reference page.
- **Authentication** with an API key (`Client::withApiKey()`), OAuth2 access tokens from
  `POST /tokens` including MFA (`Client::withAccessToken()`), or JWTs from `POST /jwt`
  (`Client::withJwt()`). Tokens renew before they expire, are re-issued after a 401, and can be
  shared through any PSR-16 cache (`Auth\Psr16TokenStore`).
- **16 typed response models** in `Simtabi\NetSapiens\Data`: typed fields, and the full payload
  through `toArray()`.
- **Typed exceptions** for each error status (`ValidationException`, `AuthenticationException`,
  `AuthorizationException`, `NotFoundException`, `ConflictException`, `ServerException`), all
  extending `ApiException` and `NetSapiensException`. `TransportException` covers a request that
  got no response.
- `Support\Paginator::each()` walks `start`/`limit` list endpoints lazily.
- `Testing\FakeTransport` lets your tests fake the API without network access.
- `ClientConfig` accepts a bare host or a URL, and its `logTag` sends `X-NetSapiens-Log` for
  iNSight on-demand logging.
- `OAuth2::setGuzzleConfig()` passes Guzzle options to the v1 token request.

### Changed

- **PHP 8.1 is now the minimum** (was 8.0, which CI never tested). Dependency ranges are caret
  ranges: `guzzlehttp/guzzle ^7.5`, `phpfastcache/phpfastcache ^9.1.3`, PHPUnit
  `^10.5 || ^11.0 || ^12.0`. `psr/simple-cache ^2.0 || ^3.0` is new.
- The README and `docs/` follow the documentation standard. The v1 usage guide moved to
  `docs/tools/legacy-v1.md`.

### Deprecated

- **The v1 client**, for removal in 2.0.0 at the earliest: `NetSapiens`,
  `Resources\Auth\OAuth2`, `Resources\Request\Request`, `Resources\Resource`, all 61
  `Resources\Objects\*` classes, `Helpers\Helpers`, the `HasErrorStorage` and
  `HasHelperFunctions` traits, and the `netSapiensClient()` helper. docs.ns-api.com no longer
  documents the v1 API. Everything still works, and each class names its v2 replacement.

### Security

- **The v1 client verifies TLS certificates by default.** It used to turn verification off
  unless the caller turned it on, which exposed the bearer token to anyone on the network path.
  `verify => false`, or a CA bundle path, in the Guzzle config still works.
- **A failed login no longer puts secrets in the exception.** The request an
  `AuthenticationException` carries has its password, client secret, MFA passcode and tokens
  replaced with `[redacted]`.
- **v2 path values that are empty, `.` or `..` are refused** before anything is sent, so a value
  cannot step out of its path segment.
- `SECURITY.md` names the reporting channels and what is in scope. Dependabot watches composer and
  GitHub Actions, and CI runs with read-only repository permissions.

### Fixed

- **v1 methods send requests again.** They gave Guzzle an array `body`, which Guzzle 7 refuses;
  they now send form fields.
- **v1 resources accept a `null` cacher**, which every constructor and `getInstance()` default to.
  It was a `TypeError`. `null` now means the token response is not cached.
- `Domain::create()` sent `action=read`, and `Device::setDevicePassword()` targeted
  `object=queuedcall`. Both now send their own action.
- `OAuth2::generateRefreshToken()` sent the stored password as the refresh token instead of the
  token it was given.
- **`OAuth2` and `Request` load again.** They used `pheg()` and
  `Simtabi\Laranail\Nails\General\Traits\HasErrorStorage`, and `composer.json` required
  neither; the `nails` package no longer exists, so neither class could be loaded. The grant-type
  check is now `strcasecmp()` (pheg's comparison was case-insensitive too), and the error storage is
  an in-package trait, `Simtabi\NetSapiens\Traits\HasErrorStorage`, with the same public methods.

## [1.0.0] - 2023-03-13

- Initial release.

[Unreleased]: https://github.com/simtabi/netsapiens-api/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/simtabi/netsapiens-api/releases/tag/v1.1.0
[1.0.0]: https://github.com/simtabi/netsapiens-api/commit/ddf9c4a
