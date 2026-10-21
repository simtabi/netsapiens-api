# Legacy v1 client

The `Simtabi\NetSapiens\NetSapiens` client for the v1 `ns-api/?object=…&action=…` API. It is deprecated since 1.1.0 and may be removed in 2.0.0 at the earliest.

docs.ns-api.com now documents only v2, so new code should use [`Client`](../getting-started.md). The v1 client still works for existing callers, and 1.1.0 repaired the defects that had stopped it working; see the [changelog](../../CHANGELOG.md).

## Creating the client

```php
use Simtabi\NetSapiens\Exceptions\NetSapiensException;
use Simtabi\NetSapiens\Helpers\Helpers;
use Simtabi\NetSapiens\NetSapiens;

try {
    $netSapiensClient = NetSapiens::getInstance(
        clientId     : $clientId,
        clientSecret : $clientSecret,
        username     : $username,
        password     : $password,
        baseUrl      : 'https://pbx.example.com/',
        cacher       : function (array $args) use ($cachePath) {
            return Helpers::cacher(
                items:             $args[0],
                cacheId:           $args[1],
                cachePath:         $cachePath,
                lifeTimeInSeconds: Helpers::CACHE_LIFETIME_IN_SECONDS,
                resetCache:        true,
            );
        },
        guzzleConfig : [],
    );
} catch (NetSapiensException $exception) {
    echo $exception->getMessage();
}
```

`cacher` receives `[$tokenResponse, $cacheKey]` and returns the token response to use, so you can supply your own cache. Since 1.1.0 it may be `null`, which means no caching.

`getInstance()` returns a singleton. A second call with different credentials returns the first client unchanged; v2's `Client` has no such restriction.

`helpers/helpers.php` defines `netSapiensClient(...)` with the same arguments. It returns the client, or the error message as a string. That file is not in Composer's autoload, so `require` it yourself if you use it.

## Authentication object and errors

```php
$oAuth2 = $netSapiensClient->getOAuth2();
$errors = $netSapiensClient->getOAuth2()->getErrors();
```

v1 methods do not throw on API errors. They record the error and return `['status' => false, ...]`, and the errors are read from the request:

```php
$callQueue = $netSapiensClient->getCallQueue()->setQueue($queue)->setUid($uid);
$result    = $callQueue->create($phoneNumber);
$errors    = $callQueue->getRequest()->getErrors();
```

## Classes and their v2 replacements

Only five classes ever had methods; the rest were placeholders.

| v1 class (`Resources\Objects\…`) | v1 methods | v2 replacement |
|---|---:|---|
| `Address` | 0 | `addresses()` |
| `Agent` | 0 | `agents(), agentActions()` |
| `AgentLog` | 0 | `callCenterStatistics()` |
| `AnswerRule` | 0 | `answerRules()` |
| `Audio` | 0 | `greetings(), voicemails(), mediaHoldMessagesUser(), mediaHoldMessagesDomain(), mediaMusicOnHoldUser(), mediaMusicOnHoldDomain(), textToSpeech()` |
| `CDR2\CDR2` | 0 | `cdrs()` |
| `CDR2\CDRExport` | 0 | `cdrs()` |
| `CDR2\CDRSchedule` | 0 | `cdrSchedule()` |
| `Call\Call` | 0 | `calls()` |
| `Call\CallCenterStats` | 0 | `callCenterStatistics()` |
| `Call\CallQueue` | 5 | `callQueues(), queuedCalls()` |
| `Call\CallQueueEmailReport` | 0 | none documented |
| `Call\CallQueueStats` | 0 | `callCenterStatistics()` |
| `Call\CallRequest` | 0 | `calls()` |
| `Call\CallerIDEmergency` | 0 | none documented |
| `Chart` | 0 | `charts()` |
| `Conference\Conference` | 0 | `conferences()` |
| `Conference\ConferenceParticipant` | 0 | `conferenceParticipants()` |
| `Conference\ConferenceRecord` | 0 | none documented |
| `Connection` | 0 | `connections()` |
| `Contact` | 0 | `contacts(), sharedContacts()` |
| `Dashboard` | 0 | `dashboards()` |
| `DefaultReader` | 0 | none documented |
| `Department` | 0 | `departments()` |
| `Device\Device` | 3 | `devices()` |
| `Device\DeviceModel` | 0 | `phoneModels()` |
| `Device\DeviceProfile` | 0 | `phoneTemplates()` |
| `Dial\DialPlan` | 0 | `dialRules()` |
| `Dial\DialPolicy` | 0 | `dialPermissions()` |
| `Dial\DialRule` | 0 | `dialRules()` |
| `Domain` | 2 | `domains()` |
| `Image` | 0 | `images()` |
| `MAC` | 0 | `phones()` |
| `Meeting` | 0 | `meetings(), iotumMeetings(), meetingEventLogs()` |
| `Message\Message` | 0 | `messages()` |
| `Message\MessageSession` | 0 | `messages()` |
| `NDPServer` | 0 | `phoneModels()` |
| `Permission` | 0 | `dialPermissions()` |
| `Phone\PhoneConfiguration` | 0 | `phones()` |
| `Phone\PhoneNumber` | 2 | `phoneNumbers(), phoneNumberUseCases()` |
| `Presence` | 0 | `presence()` |
| `Queued` | 0 | `queuedCalls()` |
| `Quota` | 0 | `quotas()` |
| `Recording` | 0 | `recordings()` |
| `Reseller` | 0 | `resellers()` |
| `Route` | 0 | `routes()` |
| `SFU` | 0 | none documented |
| `SMSNumber` | 0 | `smsNumbers()` |
| `ServerInfo` | 0 | `version()` |
| `Sites\Site` | 0 | `sites()` |
| `Sites\Sites` | 0 | `sites()` |
| `Subscriber` | 4 | `users()` |
| `Subscription` | 0 | `subscriptions()` |
| `Time\TimeFrame` | 0 | `timeframesDomain(), timeframesUser()` |
| `Time\TimeRange` | 0 | `timeframesDomainSpecificDates(), timeframesUserSpecificDates()` |
| `Trace` | 0 | `callTraces()` |
| `Turn` | 0 | none documented |
| `UCInbox` | 0 | none documented |
| `UIConfig` | 0 | `configurations(), configurationDefinitions()` |
| `Upload` | 0 | none documented |
| `VoicemailReminders` | 0 | `voicemailReminders()` |

## Migrating

See [Migrate from the v1 client](../recipes/migrate-from-v1.md).

---

[← Docs index](../../README.md#documentation)
