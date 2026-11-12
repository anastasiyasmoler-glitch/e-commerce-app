<?php

namespace App\Tracing;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class Tracer
{
    /** @var array{traceId: string, spanId: string}|null */
    private ?array $remoteParent = null;

    /** @var list<array{name: string, traceId: string, spanId: string, parentSpanId: string, start: int}> */
    private array $stack = [];

    /** @var list<array{name: string, traceId: string, spanId: string, parentSpanId: string, start: int, end: int}> */
    private array $finished = [];

    public function enabled(): bool
    {
        return (string) config('tracing.endpoint') !== '';
    }

    public function extractTraceparent(?string $header): void
    {
        if (! is_string($header) || $header === '') {
            return;
        }

        $parts = explode('-', $header);
        if (count($parts) < 4 || strlen($parts[1]) !== 32 || strlen($parts[2]) !== 16) {
            return;
        }

        $this->remoteParent = [
            'traceId' => $parts[1],
            'spanId' => $parts[2],
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function extractFromPayload(array $payload): void
    {
        $value = $payload['traceparent'] ?? null;
        $this->extractTraceparent(is_string($value) ? $value : null);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function inject(array $payload): array
    {
        $header = $this->traceparent();
        if ($header !== null) {
            $payload['traceparent'] = $header;
        }

        return $payload;
    }

    public function traceparent(): ?string
    {
        $current = $this->stack === [] ? null : $this->stack[array_key_last($this->stack)];
        if ($current === null) {
            return null;
        }

        return '00-'.$current['traceId'].'-'.$current['spanId'].'-01';
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function run(string $name, callable $callback): mixed
    {
        $this->startSpan($name);
        try {
            return $callback();
        } finally {
            $this->endSpan();
            if ($this->stack === []) {
                $this->flush();
            }
        }
    }

    public function startSpan(string $name): void
    {
        $parent = $this->stack === [] ? $this->remoteParent : $this->stack[array_key_last($this->stack)];
        $this->stack[] = [
            'name' => $name,
            'traceId' => $parent['traceId'] ?? bin2hex(random_bytes(16)),
            'spanId' => bin2hex(random_bytes(8)),
            'parentSpanId' => $parent['spanId'] ?? '',
            'start' => $this->nowNano(),
        ];
    }

    public function endSpan(): void
    {
        $span = array_pop($this->stack);
        if ($span === null) {
            return;
        }

        $span['end'] = $this->nowNano();
        $this->finished[] = $span;
    }

    public function flush(): void
    {
        $this->remoteParent = null;

        if ($this->finished === [] || ! $this->enabled()) {
            $this->finished = [];

            return;
        }

        $spans = $this->finished;
        $this->finished = [];

        try {
            Http::timeout(1)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post(rtrim((string) config('tracing.endpoint'), '/').'/v1/traces', $this->otlp($spans));
        } catch (ConnectionException|Throwable) {
            // Tracing must not break checkout.
        }
    }

    /**
     * @param  list<array{name: string, traceId: string, spanId: string, parentSpanId: string, start: int, end: int}>  $spans
     * @return array<string, mixed>
     */
    private function otlp(array $spans): array
    {
        $encoded = [];
        foreach ($spans as $span) {
            $row = [
                'traceId' => $span['traceId'],
                'spanId' => $span['spanId'],
                'name' => $span['name'],
                'kind' => 1,
                'startTimeUnixNano' => (string) $span['start'],
                'endTimeUnixNano' => (string) $span['end'],
            ];
            if ($span['parentSpanId'] !== '') {
                $row['parentSpanId'] = $span['parentSpanId'];
            }
            $encoded[] = $row;
        }

        return [
            'resourceSpans' => [[
                'resource' => [
                    'attributes' => [[
                        'key' => 'service.name',
                        'value' => ['stringValue' => (string) config('tracing.service_name', 'order')],
                    ]],
                ],
                'scopeSpans' => [[
                    'scope' => ['name' => 'e-commerce-saga'],
                    'spans' => $encoded,
                ]],
            ]],
        ];
    }

    private function nowNano(): int
    {
        return (int) round(microtime(true) * 1_000_000_000);
    }
}
