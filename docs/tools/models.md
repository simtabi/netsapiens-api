# Models

16 typed response models in `Simtabi\NetSapiens\Data`, generated from the schemas on docs.ns-api.com.

## Shape

Each model is a `final` class with one `public readonly` property per documented field. The property name is the field name in camelCase, so `music-on-hold-enabled` becomes `$musicOnHoldEnabled` and `user_email` becomes `$userEmail`. Each property's docblock gives the original field name.

```php
$domain = $ns->domains()->createDomain(['domain' => 'example.com', 'reseller' => 'acme']);

$domain->domain;        // 'example.com'
$domain->toArray();     // the payload exactly as received, including undocumented fields
```

## Coercion

Values are coerced to the documented type when they can be. `"12"` becomes `12`, and `"yes"`, `"true"`, `"1"` and `"on"` become `true`. A value that cannot be coerced reads as `null`, so a server that formats a field differently never throws. Use `toArray()` to see what was actually sent.

## Models

| Class | Schema | Typed fields |
|---|---|---:|
| `Data\AccessToken` | `AccessToken` | 18 |
| `Data\ActiveCall` | `ActiveCall` | 23 |
| `Data\Agent` | `Agent` | 25 |
| `Data\ApiKey` | `ApiKey` | 13 |
| `Data\CallQueue` | `CallQueue` | 28 |
| `Data\Cdr` | `CdrD` | 99 |
| `Data\Count` | `Count` | 1 |
| `Data\Device` | `Device` | 39 |
| `Data\Domain` | `Domain` | 42 |
| `Data\JwtToken` | `JwtRequestSuccess` | 2 |
| `Data\Message` | `Message` | 21 |
| `Data\PhoneNumber` | `Phonenumber` | 9 |
| `Data\Reseller` | `Reseller` | 10 |
| `Data\Subscription` | `Subscription` | 15 |
| `Data\SuccessResponse` | `SuccessResponse` | 2 |
| `Data\User` | `User` | 55 |

Responses whose schema is not in this list are returned as decoded JSON.

---

[← Docs index](../../README.md#documentation)
