<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Resources\V2\Resource;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

/**
 * Every generated method sends the verb and path its docs.ns-api.com page documents.
 *
 * The fixture is written by .dev/generate.php alongside the classes. The counts below are floors
 * measured on 2026-10-21 (414 operations, 78 sections), so a generator run that silently drops
 * operations fails here instead of shipping a smaller SDK.
 */
final class ResourceContractTest extends TestCase
{
    private const MIN_OPERATIONS = 414;

    private const MIN_RESOURCES = 78;

    /** Measured 2026-10-21: operations taking query parameters, and the parameters across them. */
    private const MIN_QUERY_OPERATIONS = 61;

    private const MIN_QUERY_PARAMETERS = 210;

    /** Measured 2026-10-21: operations with a JSON body, a multipart body, and a typed return. */
    private const MIN_JSON_BODIES = 143;

    private const MIN_MULTIPART_BODIES = 5;

    private const MIN_TYPED_RETURNS = 146;

    /** @return list<array{class: class-string, method: string, verb: string, path: string, params: array<string, string>, query: array<string, string>, body: 'json'|'multipart'|null, returns: array{0: string, 1: class-string|null}|null, page: string}> */
    private static function operations(): array
    {
        return require dirname(__DIR__) . '/Fixtures/operations.php';
    }

    public function test_every_operation_sends_its_documented_verb_and_path(): void
    {
        $fake   = new FakeTransport(Response::json(200, []));
        $client = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);
        $checked = 0;

        foreach (self::operations() as $op) {
            $values = [];
            $args   = [];
            foreach ((new ReflectionMethod($op['class'], $op['method']))->getParameters() as $i => $param) {
                if ($param->isOptional()) {
                    break;
                }
                $type   = $param->getType() instanceof ReflectionNamedType ? $param->getType()->getName() : 'string';
                $value  = match ($type) {
                    'int'   => 7,
                    'float' => 1.5,
                    'bool'  => true,
                    'array' => [],
                    default => "v{$i}~@",
                };
                $args[] = $value;
                $values[$param->getName()] = $value;
            }

            $accessor = lcfirst((new ReflectionClass($op['class']))->getShortName());
            $client->{$accessor}()->{$op['method']}(...$args);

            $path = [];
            foreach ($op['params'] as $apiName => $argName) {
                $path[$apiName] = (string) $values[$argName];
            }

            $sent = $fake->last();
            $label = "{$op['class']}::{$op['method']}";
            $this->assertSame($op['verb'], $sent?->method, $label);
            $this->assertSame(Resource::expand($op['path'], $path), $sent?->path, $label);
            $this->assertStringNotContainsString('{', (string) $sent?->path, $label);
            $checked++;
        }

        $this->assertGreaterThanOrEqual(self::MIN_OPERATIONS, $checked);
    }

    /**
     * Every documented query parameter reaches the query string under its API name, and the body
     * reaches the request in the form the page documents.
     */
    public function test_every_operation_sends_its_documented_query_and_body(): void
    {
        $fake       = new FakeTransport(Response::json(200, []));
        $client     = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);
        $json       = ['user' => '1001', 'nested' => ['a' => 1]];
        $parts      = [['name' => 'file', 'contents' => 'RIFF', 'filename' => 'greeting.wav']];
        $queryOps   = $queryParams = $jsonBodies = $multipartBodies = 0;

        foreach (self::operations() as $op) {
            $label  = "{$op['class']}::{$op['method']}";
            $args   = [];
            $values = [];
            foreach ((new ReflectionMethod($op['class'], $op['method']))->getParameters() as $i => $param) {
                $type  = $param->getType() instanceof ReflectionNamedType ? $param->getType()->getName() : 'mixed';
                $name  = $param->getName();
                $value = match (true) {
                    $name === 'body'      => $json,
                    $name === 'multipart' => $parts,
                    $type === 'int'       => 40 + $i,
                    $type === 'float'     => 2.5,
                    $type === 'bool'      => true,
                    default               => "v{$i}",
                };
                $args[$name]   = $value;
                $values[$name] = $value;
            }

            $client->{lcfirst((new ReflectionClass($op['class']))->getShortName())}()->{$op['method']}(...$args);
            $sent = $fake->last();

            $expected = [];
            foreach ($op['query'] as $apiName => $argName) {
                $this->assertArrayHasKey($argName, $values, "{$label} has an argument for query parameter {$apiName}");
                $expected[$apiName] = is_bool($values[$argName]) ? ($values[$argName] ? 'true' : 'false') : $values[$argName];
            }
            $this->assertSame($expected, $sent?->query, "{$label} query");

            match ($op['body']) {
                'json'      => $this->assertSame($json, $sent?->body, "{$label} body"),
                'multipart' => $this->assertSame($parts, $sent?->multipart, "{$label} multipart"),
                default     => $this->assertTrue($sent?->body === null && $sent->multipart === null, "{$label} sends no body"),
            };

            $queryOps        += $op['query'] === [] ? 0 : 1;
            $queryParams     += count($op['query']);
            $jsonBodies      += $op['body'] === 'json' ? 1 : 0;
            $multipartBodies += $op['body'] === 'multipart' ? 1 : 0;
        }

        $this->assertGreaterThanOrEqual(self::MIN_QUERY_OPERATIONS, $queryOps);
        $this->assertGreaterThanOrEqual(self::MIN_QUERY_PARAMETERS, $queryParams);
        $this->assertGreaterThanOrEqual(self::MIN_JSON_BODIES, $jsonBodies);
        $this->assertGreaterThanOrEqual(self::MIN_MULTIPART_BODIES, $multipartBodies);
    }

    /**
     * An operation whose page documents a model returns that model, or a list of it; every other
     * operation returns the decoded JSON untouched.
     */
    public function test_every_operation_returns_its_documented_shape(): void
    {
        $object  = ['domain' => 'example.test', 'user' => '1001', 'total' => '3', 'undocumented' => 'kept'];
        $typed   = 0;

        foreach (self::operations() as $op) {
            $label   = "{$op['class']}::{$op['method']}";
            $kind    = $op['returns'][0] ?? 'mixed';
            $payload = $kind === 'many' ? [$object, $object] : $object;
            $fake    = new FakeTransport(Response::json(200, $payload));
            $client  = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);

            $args = [];
            foreach ((new ReflectionMethod($op['class'], $op['method']))->getParameters() as $param) {
                if ($param->isOptional()) {
                    break;
                }
                $type   = $param->getType() instanceof ReflectionNamedType ? $param->getType()->getName() : 'string';
                $args[] = match ($type) { 'int' => 1, 'float' => 1.0, 'bool' => true, 'array' => [], default => 'x' };
            }

            $result = $client->{lcfirst((new ReflectionClass($op['class']))->getShortName())}()->{$op['method']}(...$args);

            if ($kind === 'one') {
                $this->assertInstanceOf($op['returns'][1], $result, $label);
                $this->assertSame($object, $result->toArray(), "{$label} keeps the payload");
                $typed++;
            } elseif ($kind === 'many') {
                $this->assertIsList($result, $label);
                $this->assertCount(2, $result, $label);
                $this->assertContainsOnlyInstancesOf($op['returns'][1], $result, $label);
                $typed++;
            } else {
                $this->assertSame($object, $result, "{$label} returns the decoded JSON");
            }
        }

        $this->assertGreaterThanOrEqual(self::MIN_TYPED_RETURNS, $typed);
    }

    public function test_every_operation_links_its_reference_page(): void
    {
        $operations = self::operations();
        $pages      = array_filter(array_column($operations, 'page'));

        $this->assertCount(count($operations), $pages, 'every operation came from a reference page');

        foreach ($operations as $op) {
            $doc = (string) (new ReflectionMethod($op['class'], $op['method']))->getDocComment();
            $this->assertStringContainsString("@see https://docs.ns-api.com/reference/{$op['page']}", $doc, "{$op['class']}::{$op['method']}");
            $this->assertStringContainsString("{$op['verb']} {$op['path']}", $doc, "{$op['class']}::{$op['method']}");
        }
    }

    public function test_path_values_are_url_encoded(): void
    {
        $fake = new FakeTransport(Response::json(200, []));

        Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->users()->getUser('example.test', '1001@site/x');

        $this->assertSame('/domains/example.test/users/1001%40site%2Fx', $fake->last()?->path);
    }

    public function test_a_dot_segment_cannot_be_smuggled_through_a_path_value(): void
    {
        $fake   = new FakeTransport(Response::json(200, []));
        $client = Client::withApiKey('pbx.example.test', Credentials::key(), $fake);

        foreach (['..', '.', ''] as $value) {
            try {
                $client->users()->getUser('example.test', $value);
                $this->fail("'{$value}' was accepted as a path value");
            } catch (\InvalidArgumentException) {
                $this->assertCount(0, $fake->sent(), 'nothing was sent');
            }
        }

        $client->users()->getUser('example.test', '..1001');
        $this->assertSame('/domains/example.test/users/..1001', $fake->last()?->path, 'dots inside a value are fine');
    }

    public function test_every_public_resource_method_is_in_the_fixture(): void
    {
        $files = glob(dirname(__DIR__, 2) . '/src/Resources/V2/*.php') ?: [];
        $declared = [];
        $resources = 0;

        foreach ($files as $file) {
            $class = 'Simtabi\\NetSapiens\\Resources\\V2\\' . basename($file, '.php');
            $reflection = new ReflectionClass($class);
            if (!$reflection->isSubclassOf(Resource::class)) {
                continue;
            }
            $resources++;
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() === $class) {
                    $declared[] = "{$class}::{$method->getName()}";
                }
            }
        }

        $fixture = array_map(static fn (array $op): string => "{$op['class']}::{$op['method']}", self::operations());
        sort($declared);
        sort($fixture);

        $this->assertGreaterThanOrEqual(self::MIN_RESOURCES, $resources);
        $this->assertGreaterThanOrEqual(self::MIN_OPERATIONS, count($declared));
        $this->assertSame($fixture, $declared);
    }

    public function test_every_resource_has_a_client_accessor(): void
    {
        $client = Client::withApiKey('pbx.example.test', Credentials::key(), new FakeTransport());
        $classes = array_unique(array_column(self::operations(), 'class'));

        foreach ($classes as $class) {
            $accessor = lcfirst((new ReflectionClass($class))->getShortName());
            $this->assertInstanceOf($class, $client->{$accessor}());
        }

        $this->assertGreaterThanOrEqual(self::MIN_RESOURCES, count($classes));
    }
}
