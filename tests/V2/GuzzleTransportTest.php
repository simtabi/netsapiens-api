<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Tests\V2;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Simtabi\NetSapiens\Exceptions\TransportException;
use Simtabi\NetSapiens\Http\GuzzleTransport;
use Simtabi\NetSapiens\Http\Request;

final class GuzzleTransportTest extends TestCase
{
    /** @var list<array{request: RequestInterface}> */
    private array $history = [];

    private function transport(mixed ...$responses): GuzzleTransport
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new GuzzleTransport('https://pbx.example.test/ns-api/v2', new GuzzleClient(['handler' => $stack]));
    }

    private function sent(): RequestInterface
    {
        return $this->history[0]['request'];
    }

    public function test_the_path_is_joined_under_the_base_uri_not_the_host_root(): void
    {
        $this->transport(new PsrResponse(200))->send(new Request('GET', '/domains/example.test', ['limit' => 5]));

        $this->assertSame('https://pbx.example.test/ns-api/v2/domains/example.test?limit=5', (string) $this->sent()->getUri());
    }

    public function test_a_body_is_sent_as_json(): void
    {
        $this->transport(new PsrResponse(202))->send(new Request('POST', 'domains', [], [], ['domain' => 'example.test']));

        $this->assertSame('application/json', $this->sent()->getHeaderLine('Content-Type'));
        $this->assertSame('{"domain":"example.test"}', (string) $this->sent()->getBody());
    }

    public function test_an_empty_body_is_sent_as_an_object(): void
    {
        $this->transport(new PsrResponse(202))->send(new Request('PUT', 'domains/x', [], [], []));

        $this->assertSame('{}', (string) $this->sent()->getBody());
    }

    public function test_multipart_parts_are_sent_as_multipart(): void
    {
        $this->transport(new PsrResponse(202))->send(new Request('POST', 'images', [], [], null, [
            ['name' => 'file', 'contents' => 'bytes', 'filename' => 'logo.png'],
        ]));

        $this->assertStringStartsWith('multipart/form-data', $this->sent()->getHeaderLine('Content-Type'));
        $this->assertStringContainsString('filename="logo.png"', (string) $this->sent()->getBody());
    }

    public function test_an_error_status_is_returned_not_thrown(): void
    {
        $response = $this->transport(new PsrResponse(404, ['X-Id' => 'a'], '{"code":404,"message":"nope"}'))
            ->send(new Request('GET', 'domains/missing'));

        $this->assertSame(404, $response->status);
        $this->assertSame('a', $response->header('x-id'));
        $this->assertSame(['code' => 404, 'message' => 'nope'], $response->decoded());
    }

    public function test_no_response_at_all_is_a_transport_exception(): void
    {
        $this->expectException(TransportException::class);

        $this->transport(new ConnectException('refused', new PsrRequest('GET', 'x')))->send(new Request('GET', 'domains'));
    }
}
