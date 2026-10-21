# Walk every user in a domain

Use `Paginator::each()` over `getUsers()` to visit every user without loading them all at once.

```php
use Simtabi\NetSapiens\Support\Paginator;

foreach (Paginator::each(fn (int $start, int $limit) => $ns->users()->getUsers('example.com', start: $start, limit: $limit), limit: 1000) as $user) {
    echo $user->user, PHP_EOL;
}
```

See [Pagination](../tools/pagination.md).

---

[← Docs index](../../README.md#documentation)
