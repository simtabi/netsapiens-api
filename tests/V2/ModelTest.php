<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Client;
use Simtabi\NetSapiens\Data\Count;
use Simtabi\NetSapiens\Data\Domain;
use Simtabi\NetSapiens\Data\User;
use Simtabi\NetSapiens\Testing\FakeTransport;
use Simtabi\NetSapiens\Tests\Support\Credentials;

final class ModelTest extends TestCase
{
    public function test_kebab_case_fields_become_camel_case_properties_and_the_payload_is_kept(): void
    {
        $data   = ['domain' => 'example.test', 'reseller' => 'acme', 'music-on-hold-enabled' => 'yes', 'undocumented' => 1];
        $domain = Domain::fromArray($data);

        $this->assertSame('example.test', $domain->domain);
        $this->assertSame('acme', $domain->reseller);
        $this->assertSame($data, $domain->toArray(), 'fields the docs do not list survive');
    }

    public function test_values_are_coerced_to_the_documented_type_or_left_null(): void
    {
        $this->assertSame(12, Count::fromArray(['total' => '12'])->total);
        $this->assertSame(12, Count::fromArray(['total' => 12])->total);
        $this->assertNull(Count::fromArray(['total' => 'twelve'])->total);
        $this->assertNull(Count::fromArray(['total' => '1.5'])->total);
        $this->assertNull(Count::fromArray([])->total);
    }

    public function test_every_model_builds_from_an_empty_payload(): void
    {
        $files = glob(dirname(__DIR__, 2) . '/src/Data/*.php') ?: [];
        $built = 0;

        foreach ($files as $file) {
            $class = 'Simtabi\\NetSapiens\\Data\\' . basename($file, '.php');
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }
            $this->assertSame([], $class::fromArray([])->toArray());
            $built++;
        }

        $this->assertSame(16, $built, 'one model per schema in the generator');
    }

    public function test_a_generated_method_returns_its_model(): void
    {
        $fake = (new FakeTransport())->pushJson([['user' => '1001', 'domain' => 'example.test']]);

        $users = Client::withApiKey('pbx.example.test', Credentials::key(), $fake)->users()->getUsers('example.test');

        $this->assertContainsOnlyInstancesOf(User::class, $users);
        $this->assertSame('1001', $users[0]->user);
        $this->assertSame('/domains/example.test/users', $fake->last()?->path);
    }
}
