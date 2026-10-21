# Architecture

How the v2 client is put together, and why.

## Layers

```
Client ──► Authenticator ──► Transport ──► NetSapiens /ns-api/v2
  │            │                 │
  │            └ API key, access token, JWT (+ TokenStore)
  │                              └ GuzzleTransport, FakeTransport
  └ 78 resource accessors ──► generated methods ──► Data\* models
```

- `Client` adds `Accept`, `User-Agent` and the optional log tag, asks the authenticator for the `Authorization` header, sends the request, retries once after a 401 when the authenticator can renew, and turns an error status into an exception.
- `Transport` is the only thing that touches the network. Tests substitute `FakeTransport`.
- `Authenticator` is the only thing that knows about credentials.
- `Resources\V2\*` are generated: each method fills in its path template, passes its documented query parameters, and maps the response onto a model when the schema has one.

## Why are the resources generated?

The API has 414 operations. Writing them by hand is how the previous client ended up with 61 classes, 55 of them empty and one sending the wrong action. `.dev/generate.php` reads an OpenAPI snapshot rebuilt from the reference pages on docs.ns-api.com and writes the resources, the accessors, the models and a contract-test fixture. The output is committed and reviewed like any other code.

The snapshot is NetSapiens' material and is not kept in this repository. To regenerate, rebuild it from the `OpenAPI definition` block on each page listed in <https://docs.ns-api.com/llms.txt>, then run:

```bash
php .dev/generate.php path/to/ns-api-v2-openapi.json
```

The generator corrects one path typo in the published docs (`/users/{user}number-filters`) and three title misspellings that would otherwise become method names. Both lists are in the generator.

## Why do models keep the raw payload?

The documentation describes fields, but servers differ by version and configuration. Each model types the documented fields and keeps the whole payload in `toArray()`, so a field the docs omit is never lost. When a field arrives in a different format, it reads as `null` rather than throwing.

## Why is the v1 client still here?

The v1 `NetSapiens` class and its resources are deprecated, not removed, so existing callers keep working. See [Legacy v1 client](tools/legacy-v1.md) and [Release](release.md).

---

[← Docs index](../README.md#documentation)
