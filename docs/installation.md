# Installation

How to add the SDK to a project and what it needs.

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.1 to 8.4 (tested in CI) |
| `guzzlehttp/guzzle` | ^7.5 |
| `psr/simple-cache` | ^2.0 or ^3.0, for the optional PSR-16 token store |
| `phpfastcache/phpfastcache` | ^9.1.3, used only by the deprecated v1 `Helpers::cacher()` |

## Add the repository

The package is not on Packagist yet. Point Composer at the GitHub repository, then require it:

```bash
composer config repositories.netsapiens-api vcs https://github.com/simtabi/netsapiens-api
composer require simtabi/netsapiens-api
```

## Credentials

Every v2 call is authenticated with a bearer token. You need one of:

- an API key (`nss_…`) created in the NetSapiens Admin UI, or with `POST /apikeys`;
- an OAuth client ID and secret, plus the username and password of the user to act as.

[Authentication](tools/authentication.md) compares the options.

---

[← Docs index](../README.md#documentation)
