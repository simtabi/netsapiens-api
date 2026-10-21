# Resources

78 accessors on `Simtabi\NetSapiens\Client`, one per section of [docs.ns-api.com](https://docs.ns-api.com/reference), covering 414 operations.

## Method names

A method is named after its page's `operationId` when the page has one (`GetDomains` becomes `getDomains()`), and otherwise after the page title (*Read Answerrules for a User* becomes `readAnswerrulesForUser()`). Each method's docblock gives its verb, its path and a link to its page.

## Arguments

In order:

1. path parameters, as strings, in the order they appear in the path;
2. required query parameters;
3. optional query parameters, each defaulting to `null`, which leaves it out of the request;
4. `$body` (an array sent as JSON) or `$multipart` (Guzzle-style parts for file uploads).

Path values are URL-encoded, so `1001@site/x` stays one segment. A path value that is empty, `.` or `..` throws `InvalidArgumentException` before anything is sent, because a server that normalises the path would otherwise answer a different endpoint.

The order of query parameters follows the documentation and is not alphabetical, so pass them by name:

```php
$ns->users()->getUsers('example.com', start: 0, limit: 50, site: 'HQ');
```

## Return values

| The page documents | The method returns |
|---|---|
| one of the 16 [models](models.md) | that model |
| a list of a model | `list<Model>` |
| anything else | the decoded JSON as `mixed` (array, scalar, or `null` for an empty body) |

## Accessors

| Accessor | Section on docs.ns-api.com | Operations |
|---|---|---:|
| `accessLog()` | Access Log | 1 |
| `accessTokens()` | Authentication/Access Token (Oauth - Username/Password) | 3 |
| `addresses()` | Addresses | 15 |
| `agentActions()` | Call Center/Agent Actions | 4 |
| `agents()` | Call Center/Agents | 7 |
| `answerRules()` | Answer Rules | 8 |
| `apiKeys()` | Authentication/API Key (Machine 2 Machine) | 6 |
| `auditLog()` | Audit Log | 1 |
| `autoAttendant()` | Auto Attendant | 5 |
| `backupRestore()` | Backup & Restore | 4 |
| `callBlocking()` | Call Blocking | 6 |
| `callCenterStatistics()` | Call Center/Statistics | 11 |
| `callQueues()` | Call Center/Callqueues | 6 |
| `callTraces()` | Call Traces & Cradle to Grave | 1 |
| `calls()` | Calls (live/active calls) | 13 |
| `cdrSchedule()` | CDR Schedule | 3 |
| `cdrs()` | CDR (Call History) | 6 |
| `certificates()` | SSL Certificates | 5 |
| `charts()` | Charts | 2 |
| `conferenceParticipants()` | Conference/Participants | 4 |
| `conferences()` | Conference/Conferences | 7 |
| `configurationDefinitions()` | Configs/Configuration Definitions | 5 |
| `configurations()` | Configs/Configurations | 6 |
| `connections()` | Connections | 7 |
| `contacts()` | Contacts | 7 |
| `dashboards()` | Dashboards | 2 |
| `departments()` | Departments | 1 |
| `devices()` | Devices | 10 |
| `dialPermissions()` | Dial Permisions | 18 |
| `dialRules()` | Dialrule | 11 |
| `domains()` | Domains | 9 |
| `email()` | Email | 2 |
| `firebase()` | Firebase | 2 |
| `greetings()` | Media/Greetings | 6 |
| `images()` | Images | 4 |
| `insight()` | iNSight | 1 |
| `iotumMeetings()` | Meetings/Iotum | 19 |
| `jwt()` | Authentication/JWT (JSON Web Token) | 5 |
| `mediaHoldMessagesDomain()` | Media/Hold Messages/Domain | 5 |
| `mediaHoldMessagesUser()` | Media/Hold Messages/User | 5 |
| `mediaMusicOnHoldDomain()` | Media/Music on Hold/Domain | 5 |
| `mediaMusicOnHoldUser()` | Media/Music on Hold/User | 5 |
| `meetingEventLogs()` | Meetings/Event Logs | 2 |
| `meetings()` | Meetings | 10 |
| `messages()` | Messages | 9 |
| `phoneModels()` | Phones/Macs/Supported Models and Servers | 3 |
| `phoneNumberUseCases()` | Phone Numbers/Use Cases | 1 |
| `phoneNumbers()` | Phone Numbers | 8 |
| `phoneTemplates()` | SnapBuilder/Phone Templates | 6 |
| `phones()` | Phones/Macs | 11 |
| `presence()` | Presence | 2 |
| `queuedCalls()` | Call Center/QueuedCalls | 2 |
| `quotas()` | Quotas | 4 |
| `recordings()` | Recordings | 2 |
| `resellers()` | Resellers | 6 |
| `routes()` | Routes | 10 |
| `sharedContacts()` | Contacts/Shared Contacts | 5 |
| `sites()` | Sites | 6 |
| `smsBlocks()` | Messages/SMS Block | 5 |
| `smsNumbers()` | SMS Numbers | 7 |
| `snapBuilder()` | SnapBuilder | 8 |
| `subscriptions()` | Event Subscriptions | 9 |
| `templates()` | Templates | 4 |
| `textToSpeech()` | Media/Text to Speech | 3 |
| `timeframesDomain()` | Timeframes/Domain (Shared) | 2 |
| `timeframesDomainAlways()` | Timeframes/Domain (Shared)/Always | 1 |
| `timeframesDomainHoliday()` | Timeframes/Domain (Shared)/Holiday | 2 |
| `timeframesDomainSpecificDates()` | Timeframes/Domain (Shared)/Specific Dates | 1 |
| `timeframesHolidayInformation()` | Timeframes/Holiday Information | 4 |
| `timeframesUser()` | Timeframes/User | 3 |
| `timeframesUserAlways()` | Timeframes/User/Always | 1 |
| `timeframesUserHoliday()` | Timeframes/User/Holiday | 2 |
| `timeframesUserSpecificDates()` | Timeframes/User/Specific Dates | 1 |
| `transcriptions()` | Transcriptions & Sentiment | 1 |
| `users()` | Users | 8 |
| `version()` | Version | 1 |
| `voicemailReminders()` | Voicemail Reminders | 5 |
| `voicemails()` | Media/Voicemail | 6 |

---

[← Docs index](../../README.md#documentation)
