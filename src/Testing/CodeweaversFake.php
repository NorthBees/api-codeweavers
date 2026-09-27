<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Testing;

use Closure;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Enums\Environment;
use PHPUnit\Framework\Assert;

/**
 * Fakes the Codeweavers API via Http::fake(), routing on the request path.
 */
final class CodeweaversFake
{
    /**
     * @var list<array{key: string, url: string, payload: array<array-key, mixed>}>
     */
    private array $sent = [];

    /**
     * @param  array<string, mixed>  $responses
     */
    private function __construct(private array $responses) {}

    /**
     * @param  array<string, mixed>  $responses  keyed by endpoint name (see Endpoint) or "*"
     */
    public static function fake(array $responses = []): self
    {
        $fake = new self($responses);

        $hosts = collect((array) config('codeweavers.base_urls', []))
            ->merge(array_map(fn (Environment $environment): string => $environment->defaultBaseUrl(), Environment::cases()))
            ->map(fn (mixed $url): string => (string) parse_url((string) $url, PHP_URL_HOST))
            ->filter()
            ->unique()
            ->all();

        // One callback matching exact hosts: URL patterns are wildcard-prefixed, so
        // "services.codeweavers.net/*" would also match the sandbox host.
        Http::fake(fn (Request $request): ?PromiseInterface => in_array(parse_url($request->url(), PHP_URL_HOST), $hosts, true)
            ? $fake->respond($request)
            : null);

        return $fake;
    }

    /**
     * @param  array<array-key, mixed>|PromiseInterface|Closure  $response
     */
    public function push(Endpoint|string $key, array|PromiseInterface|Closure $response): self
    {
        $this->responses[$key instanceof Endpoint ? $key->value : $key] = $response;

        return $this;
    }

    /**
     * @param  (Closure(array<array-key, mixed>): bool)|null  $callback  receives the request payload
     */
    public function assertSent(Endpoint|string $key, ?Closure $callback = null): void
    {
        $key = $key instanceof Endpoint ? $key->value : $key;
        $matching = array_filter($this->sent, fn (array $request): bool => $request['key'] === $key && ($callback === null || $callback($request['payload'])));

        Assert::assertNotEmpty($matching, "Expected Codeweavers request [{$key}] was not sent.");
    }

    public function assertSentTimes(Endpoint|string $key, int $times): void
    {
        $key = $key instanceof Endpoint ? $key->value : $key;
        $count = count(array_filter($this->sent, fn (array $request): bool => $request['key'] === $key));

        Assert::assertSame($times, $count, "Expected Codeweavers request [{$key}] {$times} times, sent {$count}.");
    }

    public function assertNotSent(Endpoint|string $key): void
    {
        $this->assertSentTimes($key, 0);
    }

    public function assertNothingSent(): void
    {
        Assert::assertSame([], $this->sent, 'Unexpected Codeweavers requests were sent.');
    }

    /**
     * @return list<array{key: string, url: string, payload: array<array-key, mixed>}>
     */
    public function sent(): array
    {
        return $this->sent;
    }

    private function respond(Request $request): PromiseInterface
    {
        $key = Endpoint::fromPath((string) parse_url($request->url(), PHP_URL_PATH))->value ?? 'unknown';
        $payload = $request->method() === 'GET' ? [] : $request->data();

        $this->sent[] = ['key' => $key, 'url' => $request->url(), 'payload' => $payload];

        $response = $this->responses[$key] ?? $this->responses['*'] ?? null;

        if ($response === null) {
            return CodeweaversResponse::error("Codeweavers request [{$key}] was not faked.");
        }

        if ($response instanceof Closure) {
            $response = $response($payload);
        }

        return $response instanceof PromiseInterface ? $response : Http::response($response);
    }
}
