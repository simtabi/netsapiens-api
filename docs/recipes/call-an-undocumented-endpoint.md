# Call an endpoint the SDK does not cover

`Client::call()` sends any method and path under `/ns-api/v2`, with the same authentication, headers and errors as the generated methods.

```php
$result = $ns->call('GET', 'domains/example.com/features', query: ['limit' => 10]);
$audio  = $ns->call('GET', 'domains/example.com/recordings/abc/download', raw: true); // the body as a string
```

Query values that are `null` are left out, and booleans are sent as `true` or `false`.

---

[← Docs index](../../README.md#documentation)
