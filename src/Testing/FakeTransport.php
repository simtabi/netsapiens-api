<?php

declare(strict_types=1);

namespace Simtabi\NetSapiens\Testing;

use Closure;
use LogicException;
use Simtabi\NetSapiens\Http\Request;
use Simtabi\NetSapiens\Http\Response;
use Simtabi\NetSapiens\Http\Transport;

/**
 * A transport for tests: records every request and answers from a queue, so nothing reaches the
 * network. Use it in your own application's tests as well as this package's.
 */
final class FakeTransport implements Transport
{
    /** @var list<Response|Closure(Request): Response> */
    private array $queue = [];

    /** @var list<Request> */
    private array $sent = [];

    /**
     * @param Response|null $fallback answers when the queue is empty; null makes an empty queue an error
     */
    public function __construct(private readonly ?Response $fallback = null)
    {
    }

    public function push(Response|Closure ...$responses): self
    {
        foreach ($responses as $response) {
            $this->queue[] = $response;
        }

        return $this;
    }

    public function pushJson(mixed $data, int $status = 200): self
    {
        return $this->push(Response::json($status, $data));
    }

    public function send(Request $request): Response
    {
        $this->sent[] = $request;
        $next         = array_shift($this->queue) ?? $this->fallback;

        if ($next === null) {
            throw new LogicException(sprintf('FakeTransport has no response queued for %s %s', $request->method, $request->path));
        }

        return $next instanceof Closure ? $next($request) : $next;
    }

    /**
     * @return list<Request>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    public function last(): ?Request
    {
        return $this->sent === [] ? null : $this->sent[array_key_last($this->sent)];
    }
}
