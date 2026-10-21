# Migrate from the v1 client

Replace `NetSapiens::getInstance()` with a v2 factory, then move each call to its v2 method.

| v1 | v2 |
|---|---|
| `NetSapiens::getInstance($id, $secret, $user, $pass, $url, $cacher)` | `Client::withAccessToken($url, new PasswordCredentials($id, $secret, $user, $pass), new Psr16TokenStore($cache))` |
| `->getDomain()->read(['domain' => 'x'])` | `->domains()->getDomain('x')` |
| `->getDomain()->create([...])` | `->domains()->createDomain([...])` |
| `->getSubscriber()->read([...])` | `->users()->getUsers($domain)` or `->users()->getUser($domain, $user)` |
| `->getSubscriber()->count([...])` | `->users()->countUsers($domain)->total` |
| `->getPhoneNumber()->read([...])` | `->phoneNumbers()->getDomainPhonenumbers($domain)` |
| `->getDevice()->read([...])` | `->devices()->getDevices($domain, $user)` |
| `['status' => false]` plus `getErrors()` | a thrown `ApiException` subclass |

v2 field names follow the v2 schemas, which differ from v1's. Check each body against its page on docs.ns-api.com. The [Legacy v1 client](../tools/legacy-v1.md) page lists every v1 class with its v2 replacement.

---

[← Docs index](../../README.md#documentation)
