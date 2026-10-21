<?php

declare(strict_types=1);

/**
 * Generates the v2 resource classes, the response models, the Client accessors and the contract
 * test fixture from an OpenAPI snapshot of docs.ns-api.com.
 *
 *     php .dev/generate.php path/to/ns-api-v2-openapi.json
 *
 * The snapshot is rebuilt from the OpenAPI block on each reference page listed in
 * https://docs.ns-api.com/llms.txt. It is NetSapiens' material and is not kept in this repository;
 * the generated code is, and is reviewed like any other change.
 */

$specPath = $argv[1] ?? exit("usage: php .dev/generate.php <spec.json>\n");
$spec     = json_decode((string) file_get_contents($specPath), true, 512, JSON_THROW_ON_ERROR);
$root     = dirname(__DIR__);
$schemas  = $spec['components']['schemas'];

// Section names on docs.ns-api.com that read badly as class names.
const CLASS_NAMES = [
    'Authentication/Access Token (Oauth - Username/Password)' => 'AccessTokens',
    'Authentication/API Key (Machine 2 Machine)'              => 'ApiKeys',
    'Authentication/JWT (JSON Web Token)'                     => 'Jwt',
    'Calls (live/active calls)'                               => 'Calls',
    'CDR (Call History)'                                      => 'Cdrs',
    'Dial Permisions'                                         => 'DialPermissions',
    'Dialrule'                                                => 'DialRules',
    'Call Traces & Cradle to Grave'                           => 'CallTraces',
    'Transcriptions & Sentiment'                              => 'Transcriptions',
    'Backup & Restore'                                        => 'BackupRestore',
    'iNSight'                                                 => 'Insight',
    'Event Subscriptions'                                     => 'Subscriptions',
    'SSL Certificates'                                        => 'Certificates',
    'Call Center/Callqueues'                                  => 'CallQueues',
    'Call Center/Agents'                                      => 'Agents',
    'Call Center/Agent Actions'                               => 'AgentActions',
    'Call Center/Statistics'                                  => 'CallCenterStatistics',
    'Call Center/QueuedCalls'                                 => 'QueuedCalls',
    'Conference/Conferences'                                  => 'Conferences',
    'Conference/Participants'                                 => 'ConferenceParticipants',
    'Contacts/Shared Contacts'                                => 'SharedContacts',
    'Configs/Configurations'                                  => 'Configurations',
    'Configs/Configuration Definitions'                       => 'ConfigurationDefinitions',
    'Messages/SMS Block'                                      => 'SmsBlocks',
    'Meetings/Iotum'                                          => 'IotumMeetings',
    'Meetings/Event Logs'                                     => 'MeetingEventLogs',
    'Phones/Macs'                                             => 'Phones',
    'Phones/Macs/Supported Models and Servers'                => 'PhoneModels',
    'SnapBuilder/Phone Templates'                             => 'PhoneTemplates',
    'SnapBuilder'                                             => 'SnapBuilder',
    'Phone Numbers/Use Cases'                                 => 'PhoneNumberUseCases',
    'Media/Greetings'                                         => 'Greetings',
    'Media/Voicemail'                                         => 'Voicemails',
    'Media/Text to Speech'                                    => 'TextToSpeech',
];

// Schemas that get a typed model. Everything else is returned as decoded JSON.
const MODELS = [
    'AccessToken' => 'AccessToken', 'JwtRequestSuccess' => 'JwtToken', 'ApiKey' => 'ApiKey',
    'Reseller' => 'Reseller', 'Domain' => 'Domain', 'User' => 'User', 'Device' => 'Device',
    'Phonenumber' => 'PhoneNumber', 'CallQueue' => 'CallQueue', 'Agent' => 'Agent', 'CdrD' => 'Cdr',
    'Subscription' => 'Subscription', 'Message' => 'Message', 'ActiveCall' => 'ActiveCall',
    'Count' => 'Count', 'SuccessResponse' => 'SuccessResponse',
];

// Typos in the published paths, corrected so the generated call reaches the real endpoint.
const PATH_FIXES = [
    '/domains/{domain}/users/{user}number-filters' => '/domains/{domain}/users/{user}/number-filters',
];

const STOP_WORDS = ['a', 'an', 'the'];

// Misspellings in page titles, corrected before a title becomes a method name.
const TITLE_FIXES = ['Specifc' => 'Specific', 'Permisions' => 'Permissions', 'Recieve' => 'Receive'];

function words(string $text): array
{
    $text = (string) preg_replace('/\([^)]*\)/', ' ', $text);
    return array_values(array_filter(preg_split('/[^A-Za-z0-9]+/', $text) ?: [], 'strlen'));
}

function studly(string $text): string
{
    return implode('', array_map(static fn (string $w): string => ucfirst(strtolower($w)), words($text)));
}

function camel(string $text): string
{
    $words = array_values(array_filter(words($text), static fn (string $w): bool => !in_array(strtolower($w), STOP_WORDS, true)));
    $out   = strtolower($words[0] ?? 'x');
    foreach (array_slice($words, 1) as $w) {
        $out .= ucfirst(strtolower($w));
    }
    return preg_match('/^[0-9]/', $out) ? 'n' . $out : $out;
}

function propName(string $key): string
{
    // keep existing camelCase (displayName); split kebab and snake case
    if (!preg_match('/[-_ .]/', $key)) {
        return lcfirst($key);
    }
    $parts = preg_split('/[-_ .]+/', $key) ?: [$key];
    $out   = strtolower(array_shift($parts));
    foreach ($parts as $p) {
        $out .= ucfirst(strtolower($p));
    }
    return preg_match('/^[0-9]/', $out) ? 'n' . $out : $out;
}

function className(string $tag): string
{
    return CLASS_NAMES[$tag] ?? studly($tag);
}

function resolve(array $schema, array $schemas, int $depth = 0): array
{
    if ($depth > 8) {
        return [];
    }
    if (isset($schema['$ref'])) {
        return resolve($schemas[basename($schema['$ref'])] ?? [], $schemas, $depth + 1);
    }
    if (isset($schema['allOf'][0])) {
        return resolve($schema['allOf'][0], $schemas, $depth + 1);
    }
    return $schema;
}

function phpType(array $schema, array $schemas): string
{
    $s = resolve($schema, $schemas);
    return match ($s['type'] ?? null) {
        'integer' => 'int',
        'number'  => ($s['format'] ?? null) === 'int' ? 'int' : 'float',
        'boolean' => 'bool',
        'array', 'object' => 'array',
        'string'  => 'string',
        default   => 'mixed',
    };
}

function export(mixed $value): string
{
    return var_export($value, true);
}

/** @return array{0: string, 1: string} kind, model */
function returnShape(string $verb, array $op, array $schemas): array
{
    foreach (['200', '202', '201'] as $code) {
        $content = $op['responses'][$code]['content'] ?? null;
        if ($content === null) {
            continue;
        }
        // A GET answering */* is a download (audio, recordings, exports); keep its bytes.
        if ($verb === 'GET' && !isset($content['application/json']) && isset($content['*/*'])) {
            return ['raw', ''];
        }
        $schema = $content['application/json']['schema'] ?? [];
        if (isset($schema['$ref']) && isset(MODELS[basename($schema['$ref'])])) {
            return ['one', MODELS[basename($schema['$ref'])]];
        }
        if (($schema['type'] ?? null) === 'array' && isset($schema['items']['$ref']) && isset(MODELS[basename($schema['items']['$ref'])])) {
            return ['many', MODELS[basename($schema['items']['$ref'])]];
        }
        return ['mixed', ''];
    }
    return ['mixed', ''];
}

// ---- collect operations, collapsing the "#n" example variants of one path ------------------------

$ops = [];
foreach ($spec['paths'] as $rawPath => $methods) {
    $path = rtrim(explode('#', $rawPath)[0], '/') ?: '/';
    $path = PATH_FIXES[$path] ?? $path;
    foreach ($methods as $verb => $op) {
        $key = strtoupper($verb) . ' ' . $path;
        if (!isset($ops[$key])) {
            $ops[$key] = ['verb' => strtoupper($verb), 'path' => $path, 'op' => $op, 'variants' => []];
        }
        $ops[$key]['variants'][] = $op;
        // merge query parameters documented on any variant
        foreach ($op['parameters'] ?? [] as $p) {
            $names = array_map(static fn (array $q): string => $q['in'] . ':' . $q['name'], $ops[$key]['op']['parameters'] ?? []);
            if (!in_array($p['in'] . ':' . $p['name'], $names, true)) {
                $ops[$key]['op']['parameters'][] = $p;
            }
        }
        if (!isset($ops[$key]['op']['requestBody']) && isset($op['requestBody'])) {
            $ops[$key]['op']['requestBody'] = $op['requestBody'];
        }
    }
}

$byClass = [];
foreach ($ops as $entry) {
    $tag = $entry['op']['tags'][0] ?? 'General';
    $byClass[className($tag)]['tag'] = $tag;
    $byClass[className($tag)]['ops'][] = $entry;
}
ksort($byClass);

// ---- resources ---------------------------------------------------------------------------------

$fixture  = [];
$resDir   = $root . '/src/Resources/V2';
foreach (glob($resDir . '/*.php') ?: [] as $file) {
    if (!in_array(basename($file), ['Resource.php'], true)) {
        unlink($file);
    }
}

foreach ($byClass as $class => $group) {
    $methods = [];
    $used    = [];
    usort($group['ops'], static fn (array $a, array $b): int => [$a['path'], $a['verb']] <=> [$b['path'], $b['verb']]);

    foreach ($group['ops'] as $entry) {
        $op     = $entry['op'];
        $name   = isset($op['operationId']) && $op['operationId'] !== ''
            ? lcfirst((string) preg_replace('/[^A-Za-z0-9]/', '', $op['operationId']))
            : camel(strtr(trim($op['summary'] ?? strtolower($entry['verb'])), TITLE_FIXES));
        $base   = $name;
        $suffix = 2;
        while (isset($used[strtolower($name)])) {
            $name = $base . $suffix++;
        }
        $used[strtolower($name)] = true;

        preg_match_all('/\{([^}]+)\}/', $entry['path'], $m);
        $pathParams = [];
        $args       = [];
        $taken      = [];
        foreach ($m[1] as $apiName) {
            $arg = camel($apiName);
            $taken[$arg] = true;
            $pathParams[$apiName] = $arg;
            $args[] = ['code' => "string \${$arg}", 'required' => true, 'doc' => null];
        }

        $query = [];
        $optional = [];
        foreach ($op['parameters'] ?? [] as $p) {
            if (($p['in'] ?? '') !== 'query') {
                continue;
            }
            $arg = camel($p['name']);
            if (isset($taken[$arg])) {
                $arg .= 'Query';
            }
            $taken[$arg] = true;
            $type = phpType($p['schema'] ?? [], $schemas);
            $query[$p['name']] = $arg;
            if (!empty($p['required'])) {
                $args[] = ['code' => ($type === 'mixed' ? 'string' : $type) . " \${$arg}", 'required' => true];
            } else {
                $optional[] = ['code' => ($type === 'mixed' ? '' : '?' . $type . ' ') . "\${$arg} = null", 'required' => false];
            }
        }

        $content   = $op['requestBody']['content'] ?? [];
        $hasBody   = isset($content['application/json']);
        $multipart = isset($content['multipart/form-data']);
        if ($multipart) {
            $args[] = ['code' => 'array $multipart', 'required' => true];
        } elseif ($hasBody) {
            if (!empty($op['requestBody']['required'])) {
                $args[] = ['code' => 'array $body', 'required' => true];
            } else {
                $optional[] = ['code' => '?array $body = null', 'required' => false];
            }
        }
        $args = array_merge($args, $optional);

        [$kind, $model] = returnShape($entry['verb'], $op, $schemas);
        $returnType = match ($kind) {
            'raw'  => 'string',
            'one'  => 'Data\\' . $model,
            'many' => 'array',
            default => 'mixed',
        };

        $call = sprintf(
            '$this->call(%s, %s, %s, %s, %s, %s%s)',
            export($entry['verb']),
            export($entry['path']),
            $pathParams === [] ? '[]' : '[' . implode(', ', array_map(static fn ($k, $v) => export($k) . ' => $' . $v, array_keys($pathParams), $pathParams)) . ']',
            $query === [] ? '[]' : '[' . implode(', ', array_map(static fn ($k, $v) => export($k) . ' => $' . $v, array_keys($query), $query)) . ']',
            $hasBody && !$multipart ? '$body' : 'null',
            $multipart ? '$multipart' : 'null',
            $kind === 'raw' ? ', raw: true' : '',
        );
        $body = match ($kind) {
            'one'  => "return \$this->one(Data\\{$model}::class, {$call});",
            'many' => "return \$this->many(Data\\{$model}::class, {$call});",
            'raw'  => "return {$call};",
            default => "return {$call};",
        };

        $page    = basename((string) ($op['x-page'] ?? ''), '.md');
        $summary = trim(preg_replace('/\s+/', ' ', $op['summary'] ?? $name) ?? $name);
        $doc     = ["    /**", "     * {$summary}", "     *", "     * {$entry['verb']} {$entry['path']}"];
        if ($page !== '') {
            $doc[] = "     *";
            $doc[] = "     * @see https://docs.ns-api.com/reference/{$page}";
        }
        if ($multipart) {
            $doc[] = '     * @param list<array<string, mixed>> $multipart Guzzle-style parts: name, contents, filename';
        } elseif ($hasBody) {
            $doc[] = '     * @param array<array-key, mixed>' . (empty($op['requestBody']['required']) ? '|null' : '') . ' $body sent as JSON';
        }
        if ($kind === 'many') {
            $doc[] = "     * @return list<Data\\{$model}>";
        }
        $doc[] = '     */';

        $signature = implode(', ', array_column($args, 'code'));
        $methods[] = implode("\n", $doc) . "\n    public function {$name}({$signature}): {$returnType}\n    {\n        {$body}\n    }";

        $fixture[] = [
            'class'   => "Simtabi\\NetSapiens\\Resources\\V2\\{$class}",
            'method'  => $name,
            'verb'    => $entry['verb'],
            'path'    => $entry['path'],
            'params'  => $pathParams,
            'query'   => $query,
            'body'    => $multipart ? 'multipart' : ($hasBody ? 'json' : null),
            'returns' => $kind === 'mixed' ? null : [$kind, $model === '' ? null : "Simtabi\\NetSapiens\\Data\\{$model}"],
            'page'    => $page,
        ];
    }

    $usesData = str_contains(implode('', $methods), 'Data\\');
    $code = "<?php\n\n// Generated by .dev/generate.php from docs.ns-api.com. Regenerate rather than edit.\n\ndeclare(strict_types=1);\n\nnamespace Simtabi\\NetSapiens\\Resources\\V2;\n\n"
        . ($usesData ? "use Simtabi\\NetSapiens\\Data;\n\n" : '')
        . "/**\n * {$group['tag']}\n */\nfinal class {$class} extends Resource\n{\n" . implode("\n\n", $methods) . "\n}\n";
    file_put_contents("{$resDir}/{$class}.php", $code);
}

// ---- accessors ---------------------------------------------------------------------------------

$acc = [];
foreach ($byClass as $class => $group) {
    $acc[] = "    /**\n     * {$group['tag']}\n     */\n    public function " . lcfirst($class) . "(): {$class}\n    {\n        return \$this->resource({$class}::class);\n    }";
}
file_put_contents("{$resDir}/Accessors.php", "<?php\n\n// Generated by .dev/generate.php from docs.ns-api.com. Regenerate rather than edit.\n\ndeclare(strict_types=1);\n\nnamespace Simtabi\\NetSapiens\\Resources\\V2;\n\n/**\n * One accessor per section of docs.ns-api.com, used by Simtabi\\NetSapiens\\Client.\n */\ntrait Accessors\n{\n" . implode("\n\n", $acc) . "\n}\n");

// ---- models ------------------------------------------------------------------------------------

$dataDir = $root . '/src/Data';
foreach (MODELS as $schemaName => $model) {
    $props = resolve($schemas[$schemaName], $schemas)['properties'] ?? [];
    $params = ['        public readonly array $raw = [],'];
    $assign = ['            raw: $data,'];
    $seen = ['raw' => true];
    foreach ($props as $key => $prop) {
        $p = propName((string) $key);
        if (isset($seen[$p])) {
            $p .= 'Field';
        }
        $seen[$p] = true;
        $type = phpType($prop, $schemas);
        $cast = ['string' => 'str', 'int' => 'int', 'float' => 'float', 'bool' => 'bool', 'array' => 'arr', 'mixed' => ''][$type];
        $params[] = "        /** {$key} */\n        public readonly " . ($type === 'mixed' ? 'mixed' : "?{$type}") . " \${$p} = null,";
        $assign[] = "            {$p}: " . ($cast === '' ? "\$data[" . export((string) $key) . "] ?? null" : "self::{$cast}(\$data[" . export((string) $key) . "] ?? null)") . ',';
    }
    $code = "<?php\n\n// Generated by .dev/generate.php from the {$schemaName} schema on docs.ns-api.com. Regenerate rather than edit.\n\ndeclare(strict_types=1);\n\nnamespace Simtabi\\NetSapiens\\Data;\n\nfinal class {$model} extends Model\n{\n    /**\n     * @param array<string, mixed> \$raw the payload as received\n     */\n    public function __construct(\n"
        . implode("\n", $params) . "\n    ) {\n    }\n\n    public static function fromArray(array \$data): static\n    {\n        return new self(\n"
        . implode("\n", $assign) . "\n        );\n    }\n\n    public function toArray(): array\n    {\n        return \$this->raw;\n    }\n}\n";
    file_put_contents("{$dataDir}/{$model}.php", $code);
}

// ---- contract fixture --------------------------------------------------------------------------

@mkdir($root . '/tests/Fixtures', 0777, true);
file_put_contents($root . '/tests/Fixtures/operations.php', "<?php\n\n// Generated by .dev/generate.php. One row per v2 operation on docs.ns-api.com.\n\ndeclare(strict_types=1);\n\nreturn " . var_export($fixture, true) . ";\n");

printf("%d operations, %d resource classes, %d models\n", count($fixture), count($byClass), count(MODELS));
