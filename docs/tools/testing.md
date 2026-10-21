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
- **Against a real server (opt in):** `tests/Live/LiveEndpointsTest.php` is excluded from `composer test` and runs with `composer test-live`. See below.
- **Authentication, errors, paging, models:** each has its own test, and so do the v1 repairs and the deprecation markers.

## Testing against a real server

`composer test-live` calls a NetSapiens server you point it at. It only reads: it runs each GET operation that needs no arguments, or only a domain (89 in all), asking for one row where the call takes `limit`. It never creates, changes or deletes anything. `/accesslog` and `/auditlog` are left out, because they take no `limit` and can be very large.

```bash
export NETSAPIENS_LIVE_SERVER=pbx.example.com
export NETSAPIENS_LIVE_API_KEY=nss_…        # or the four OAuth variables
export NETSAPIENS_LIVE_DOMAIN=example.com   # optional; "~" (your own domain) otherwise
composer test-live
```

| Variable | Meaning |
|---|---|
| `NETSAPIENS_LIVE_SERVER` | host or URL of the server |
| `NETSAPIENS_LIVE_API_KEY` | an API key; a read-only key is enough |
| `NETSAPIENS_LIVE_CLIENT_ID`, `_CLIENT_SECRET`, `_USERNAME`, `_PASSWORD` | OAuth credentials, instead of a key |
| `NETSAPIENS_LIVE_DOMAIN` | the domain for the domain-scoped sweep |

The run prints how many calls succeeded, how many were refused (4xx), and how many failed. A refusal is reported but does not fail the run, because it depends on the key's scope and the server's licensed features. A 5xx, a call that gets no response, or a run where nothing succeeds fails it. Every call carries `X-NetSapiens-Log: simtabi-netsapiens-api-live-test`, so the run shows up in iNSight's on-demand log. Without `NETSAPIENS_LIVE_SERVER` the tests are skipped.

---

[← Docs index](../../README.md#documentation)
