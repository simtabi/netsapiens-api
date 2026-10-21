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

No test reaches the network. `tests/V2/ResourceContractTest.php` calls each of the 414 generated methods and checks its verb and path.

---

[← Docs index](../../README.md#documentation)
