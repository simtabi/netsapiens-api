# Share tokens across workers

Pass a PSR-16 cache so queue workers reuse one login instead of each posting to `/tokens`.

```php
use Simtabi\NetSapiens\Auth\PasswordCredentials;
use Simtabi\NetSapiens\Auth\Psr16TokenStore;
use Simtabi\NetSapiens\Client;

$ns = Client::withAccessToken(
    'pbx.example.com',
    new PasswordCredentials('acme-portal', getenv('NETSAPIENS_CLIENT_SECRET'), '1001@example.com', getenv('NETSAPIENS_PASSWORD')),
    new Psr16TokenStore($cache), // e.g. Laravel's Cache::store('redis'), or Symfony's Psr16Cache
);
```

See [Authentication](../tools/authentication.md#token-stores).

---

[← Docs index](../../README.md#documentation)
