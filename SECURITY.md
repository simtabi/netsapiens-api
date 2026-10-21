# Security policy

`simtabi/netsapiens-api` follows the [simtabi security policy](https://github.com/simtabi/.github/blob/HEAD/SECURITY.md).
Never report a vulnerability in a public issue.

Report it privately through GitHub's [Report a vulnerability](https://github.com/simtabi/netsapiens-api/security/advisories/new) form, or email **[security@simtabi.com](mailto:security@simtabi.com)**.

## Supported versions

| Version | Security fixes |
|---|---|
| 1.1.x | yes |
| 1.0.x and earlier | no; upgrade to 1.1 |

The v1 client (`Simtabi\NetSapiens\NetSapiens` and `Resources\Objects\*`) is deprecated but still receives security fixes for as long as 1.x is supported.

## Scope notes

This package holds credentials for a telephony platform: API keys, OAuth client secrets, user passwords, access and refresh tokens, and JWTs. These are in scope:

- a credential, token or MFA passcode reaching a log, an exception, a cache key, or any host other than the configured NetSapiens server;
- TLS verification being disabled without the caller asking for it;
- a token store serving one login's token to a different login;
- a path parameter that escapes its segment, for example by reaching a different endpoint through `/` or `..` in a value.

Vulnerabilities in the NetSapiens API itself belong to NetSapiens, not this package.
