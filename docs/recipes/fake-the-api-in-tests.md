# Fake the API in your tests

Inject a `FakeTransport` and queue the responses your code should see.

```php
$fake = (new FakeTransport())->pushJson([['domain' => 'example.test']]);
$ns   = Client::withApiKey('pbx.example.test', 'nss_test', $fake);

$service = new DomainReport($ns);   // your code
$service->run();

$this->assertSame('/domains', $fake->last()->path);
```

See [Testing](../tools/testing.md).

---

[← Docs index](../../README.md#documentation)
