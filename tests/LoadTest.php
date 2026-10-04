<?php

namespace Simtabi\NetSapiens\Tests;

use PHPUnit\Framework\TestCase;
use Simtabi\NetSapiens\Traits\HasErrorStorage;

/**
 * OAuth2 and Request used to depend on pheg() and a laranail/nails trait that composer.json never
 * required, so neither class could be loaded. These tests load them with only the declared
 * requirements installed.
 */
final class LoadTest extends TestCase
{
    public function test_the_resource_classes_load(): void
    {
        $this->assertTrue(class_exists(\Simtabi\NetSapiens\Resources\Auth\OAuth2::class));
        $this->assertTrue(class_exists(\Simtabi\NetSapiens\Resources\Request\Request::class));
    }

    public function test_the_error_storage_keeps_and_reports_errors(): void
    {
        $holder = new class () {
            use HasErrorStorage;

            public function record(): self
            {
                return $this->setErrors('client_id missing')->setErrors(['base_url' => 'invalid']);
            }
        };

        $holder->record();

        $this->assertTrue($holder->hasErrors());
        $this->assertSame(2, $holder->getErrorCount());
        $this->assertSame('client_id missing', $holder->getFirstError());
        $this->assertSame(['invalid'], $holder->getErrors('base_url'));
        $this->assertFalse($holder->clearErrors()->hasErrors());
    }
}
