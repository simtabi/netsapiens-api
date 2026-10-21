# Errors

Every error status throws a subclass of `ApiException`, which extends the package's `NetSapiensException`.

| Status | Exception |
|---|---|
| 400 | `ValidationException` |
| 401 | `AuthenticationException` (after the one retry, for flows that renew) |
| 403 | `AuthorizationException` |
| 404 | `NotFoundException` |
| 409 | `ConflictException` |
| 5xx | `ServerException` |
| any other 4xx | `ApiException` |
| no response (DNS, TLS, timeout) | `TransportException` |

All are in `Simtabi\NetSapiens\Exceptions`.

## What an exception carries

```php
try {
    $ns->users()->createUser('example.com', ['user' => '1001']);
} catch (ApiException $e) {
    $e->status;            // 409
    $e->getMessage();      // "POST /domains/example.com/users returned 409: User already exists"
    $e->request->path;     // "/domains/example.com/users"
    $e->response->body;    // the raw body
    $e->response->header('X-Request-Id');
}
```

An exception never carries your credentials. `$e->request` is the request before the `Authorization` header was added. When a login fails, its password, client secret, MFA passcode and tokens are replaced with `[redacted]`. Logging an exception or sending it to an error tracker is therefore safe.

The message ends with the `message` field of the API's `ErrorResponse` (`{code, message}`), or with the raw body when there is no such field.

## Rate limits

docs.ns-api.com documents no rate limit, no 429 status and no limit headers. A 429, if your server sends one, throws a plain `ApiException`. Read `$e->response->header('Retry-After')` if you need to back off.

---

[← Docs index](../../README.md#documentation)
