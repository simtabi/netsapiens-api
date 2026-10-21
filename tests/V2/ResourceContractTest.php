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

    /** @return list<array{class: class-string, method: string, verb: string, path: string, params: array<string, string>}> */
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

    public function test_path_values_are_url_encoded(): void
    {
        $fake = new FakeTransport(Response::json(200, []));

        Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->users()->getUser('example.test', '1001@site/x');

        $this->assertSame('/domains/example.test/users/1001%40site%2Fx', $fake->last()?->path);
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
