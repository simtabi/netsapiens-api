# Release

How versions are numbered and when deprecated code goes away.

## Versioning

Semantic versioning. Changes are collected under `[Unreleased]` in [CHANGELOG.md](../CHANGELOG.md) and released from a `vX.Y.Z` tag.

| Release | Contents |
|---|---|
| 1.1.0 | The v2 client. The v1 client is deprecated and still works. |
| 2.0.0, at the earliest | May remove the v1 client: `NetSapiens`, `Resources\Auth\OAuth2`, `Resources\Request\Request`, `Resources\Resource`, `Resources\Objects\*`, `Helpers\Helpers`, and the `HasErrorStorage` and `HasHelperFunctions` traits. |

## Regenerating for a new API release

1. Rebuild the OpenAPI snapshot from docs.ns-api.com (see [Architecture](architecture.md#why-are-the-resources-generated)).
2. Run `php .dev/generate.php <snapshot>`.
3. Review the diff. A renamed or removed method is a breaking change and needs its own decision. The generator rewrites every resource file and cannot yet keep a deprecated method, so one that must survive has to be added to the generator before the change merges.
4. If the operation count changed, update the floors in `tests/V2/ResourceContractTest.php`.
5. Run `composer test`.

---

[← Docs index](../README.md#documentation)
