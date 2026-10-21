# Pagination

List endpoints take `start` (a zero-based offset, default 0) and `limit` (1 to 1000, default 100). `Simtabi\NetSapiens\Support\Paginator` walks them.

```php
use Simtabi\NetSapiens\Support\Paginator;

$users = Paginator::each(
    fn (int $start, int $limit): array => $ns->users()->getUsers('example.com', start: $start, limit: $limit),
    limit: 500,
);

foreach ($users as $user) {
    // Data\User, one page fetched at a time
}
```

`each()` returns a generator. It requests the next page only after the current one is used up, and stops at the first page shorter than `limit`. When the total is an exact multiple of `limit`, that costs one extra request, which returns an empty page.

To get a total without fetching everything, call the matching `count…()` method, for example `$ns->users()->countUsers('example.com')->total`.

---

[← Docs index](../../README.md#documentation)
