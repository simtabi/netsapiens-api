# Getting started

From an API key to reading, writing and handling errors, in five steps.

## 1. Create a client

```php
use Simtabi\NetSapiens\Client;

$ns = Client::withApiKey('pbx.example.com', getenv('NETSAPIENS_API_KEY'));
```

The first argument is your NetSapiens server. A bare host, a URL, or a URL already ending in `/ns-api/v2` all work. [Configuration](configuration.md) covers the timeout and the iNSight log tag.

## 2. Find the method

Each section of [docs.ns-api.com](https://docs.ns-api.com/reference) is one accessor on the client, and each page in it is one method. The section *Domains* is `$ns->domains()`, and its page *Count Domains* is `countDomains()`. Each method's docblock links its page. [Resources](tools/resources.md) lists all 78 accessors.

## 3. Read

```php
$domains = $ns->domains()->getDomains(limit: 100);   // list<Simtabi\NetSapiens\Data\Domain>

foreach ($domains as $domain) {
    echo $domain->domain, ' ', $domain->description, PHP_EOL;
}

$user = $ns->users()->getUser('example.com', '1001');  // Simtabi\NetSapiens\Data\User
echo $user->toArray()['email'] ?? '';                  // every field, documented or not
```

Path parameters come first, then the documented query parameters as optional arguments. Use named arguments for the query parameters, because their order follows the API documentation.

## 4. Write

Request bodies are plain arrays, sent as JSON with the field names the API documents:

```php
$ns->users()->createUser('example.com', [
    'user'            => '1002',
    'name-first-name' => 'Ada',
    'name-last-name'  => 'Lovelace',
]);
```

## 5. Handle errors

An error status throws. Each common status has its own exception, and all of them extend `ApiException`:

```php
use Simtabi\NetSapiens\Exceptions\ApiException;
use Simtabi\NetSapiens\Exceptions\NotFoundException;

try {
    $ns->domains()->getDomain('missing.example.com');
} catch (NotFoundException $e) {
    // 404
} catch (ApiException $e) {
    error_log($e->getMessage()); // "GET /domains/missing.example.com returned 404: …"
}
```

[Errors](tools/errors.md) lists the exception for each status.

---

[← Docs index](../README.md#documentation)
