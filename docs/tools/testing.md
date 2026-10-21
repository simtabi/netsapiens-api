# Testing

`Simtabi\NetSapiens\Testing\FakeTransport` stands in for the network: it records every request and answers from a queue.

```php
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Testing\FakeTransport;

$fake = (new FakeTransport())
    ->pushJson(['total' => 42])
    ->push(new Response(404, '{"code":404,"message":"not found"}'));

$ns = Client::withApiKey('pbx.example.test', 'nss_test', $fake);

$ns->users()->countUsers('example.test')->total;   // 42
$fake->last()->path;                              // "/domains/example.test/users/count"
```

| Method | Does |
|---|---|
| `push(Response\|Closure ...$responses)` | queue responses. A closure receives the `Request` and returns a `Response`. |
| `pushJson(mixed $data, int $status = 200)` | queue a JSON response |
| `sent()` | every request, in order |
| `last()` | the most recent request |
| `new FakeTransport($fallback)` | answer `$fallback` once the queue is empty. With no fallback, an empty queue throws. |

The token flows use the same transport, so a test of `withAccessToken()` queues the token response first.

## This package's own tests

```bash
composer test
```

No test reaches the network. The suite checks:

- **Contracts:** `tests/V2/ResourceContractTest.php` calls each of the 414 generated methods. It checks the verb, the path, every documented query parameter under its API name, the body (JSON or multipart), the return shape (model, list of models, or decoded JSON) and the `@see` link to the reference page. Floors on each count make a generator run that drops anything fail.
- **The wire:** `tests/V2/EndToEndTest.php` runs the real `GuzzleTransport` against Guzzle's mock handler, so the exact URLs, headers and bodies are checked, including the token login.
- **Authentication, errors, paging, models:** each has its own test, and so do the v1 repairs and the deprecation markers.

---

[← Docs index](../../README.md#documentation)
