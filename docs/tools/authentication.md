# Authentication

Three ways to authenticate, one `Client` factory for each, and a token store for the two that log in.

| Factory | Sends | Logs in with | Renews | Best for |
|---|---|---|---|---|
| `Client::withApiKey()` | the API key | nothing; the key is the bearer | no | server-to-server jobs |
| `Client::withAccessToken()` | an OAuth2 access token | `POST /tokens`, password or MFA grant | refresh-token grant | acting as a user; v1-era OAuth clients |
| `Client::withJwt()` | a JWT | `POST /jwt`, password or MFA grant | refresh JWT, used once | end-user web apps |
| `Client::withToken()` | a token you already hold | nothing | no | tokens issued elsewhere |

## API key

```php
$ns = Client::withApiKey('pbx.example.com', getenv('NETSAPIENS_API_KEY'));
```

API keys can be limited by IP, scope, read-only access and allowed models. Manage them with `$ns->apiKeys()`.

## Access token and JWT

```php
use Simtabi\NetSapiens\Auth\PasswordCredentials;

$credentials = new PasswordCredentials(
    clientId: 'acme-portal',
    clientSecret: getenv('NETSAPIENS_CLIENT_SECRET'),
    username: '1001@example.com',
    password: getenv('NETSAPIENS_PASSWORD'),
);

$ns  = Client::withAccessToken('pbx.example.com', $credentials);
$jwt = Client::withJwt('pbx.example.com', $credentials);
```

The authenticator logs in on the first call and reuses the token after that. Within 30 seconds of expiry it renews with the refresh token. If renewal is refused, it logs in again. When the API answers 401, the client forgets the token, logs in again and retries the call once.

The expiry comes from `expires_in` for access tokens and from the `exp` claim for JWTs. The JWT signature is not checked: the API checks it, and the client only needs to know when to renew.

## MFA

Pass the one-time passcode from the user's authenticator app. The grant becomes `mfa`:

```php
$credentials = new PasswordCredentials('acme-portal', $secret, '1001@example.com', $password, mfaPasscode: '492817');
```

The passcode works only once, so an MFA login cannot be repeated silently. Share the token through a store (below), or prompt the user again when the token can no longer be renewed.

## Token stores

Tokens live in memory by default, so each PHP process logs in once. To share them between workers or requests, pass any PSR-16 cache:

```php
use Simtabi\NetSapiens\Auth\Psr16TokenStore;

$ns = Client::withAccessToken('pbx.example.com', $credentials, new Psr16TokenStore($psr16Cache));
```

Cache keys are a SHA-256 hash of the flow, client ID and username, under the prefix `simtabi.netsapiens.token.`. The password is never part of a key. A token that can be renewed is cached without a TTL, because its refresh token outlives the access token. A token that cannot be renewed expires from the cache along with it.

---

[← Docs index](../../README.md#documentation)
