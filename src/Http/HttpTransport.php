<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversAuthenticationException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversConnectionException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversInvalidResponseException;
use NorthBees\CodeweaversApi\Exceptions\CodeweaversRequestException;
use NorthBees\CodeweaversApi\Support\Value;
use Throwable;

/**
 * Sends JSON requests through Laravel's HTTP client, so Http::fake() works in tests.
 */
final class HttpTransport
{
    /**
     * @param  array<string, mixed>  $config  the resolved `codeweavers` config
     */
    public function __construct(private readonly array $config) {}

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @return array<string, mixed>
     *
     * @throws CodeweaversConnectionException|CodeweaversAuthenticationException|CodeweaversRequestException|CodeweaversInvalidResponseException
     */
    public function send(Endpoint $endpoint, string $baseUrl, #[\SensitiveParameter] string $apiKey, ?array $payload = null, string ...$pathParameters): array
    {
        $startedAt = microtime(true);
        $response = $this->request($endpoint, $baseUrl, $apiKey, $payload, $pathParameters);

        $this->log($endpoint, $response->status(), $startedAt);

        return $this->parse($endpoint, $response);
    }

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @param  array<int, string>  $pathParameters
     */
    private function request(Endpoint $endpoint, string $baseUrl, string $apiKey, ?array $payload, array $pathParameters): Response
    {
        $url = rtrim($baseUrl, '/').$endpoint->path(...$pathParameters);

        try {
            $request = Http::withHeaders(['X-CW-ApiKey' => $apiKey])
                ->acceptJson()
                ->timeout((int) data_get($this->config, 'timeout', 15))
                ->connectTimeout((int) data_get($this->config, 'connect_timeout', 5))
                ->retry(
                    max(1, (int) data_get($this->config, 'retry.times', 2) + 1),
                    (int) data_get($this->config, 'retry.sleep_ms', 250),
                    fn (Throwable $exception): bool => $exception instanceof ConnectionException
                        || ($exception instanceof RequestException && in_array($exception->response->status(), [429, 502, 503, 504], true)),
                    throw: false,
                );

            return $endpoint->method() === 'GET'
                ? $request->get($url)
                : $request->asJson()->post($url, $payload ?? []);
        } catch (ConnectionException $exception) {
            throw new CodeweaversConnectionException("Unable to connect to Codeweavers ({$endpoint->value}).", $endpoint, previous: $exception);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function parse(Endpoint $endpoint, Response $response): array
    {
        if ($response->serverError()) {
            throw new CodeweaversConnectionException("Codeweavers {$endpoint->value} returned HTTP {$response->status()}.", $endpoint, $response->status());
        }

        $body = $response->json();

        if ($response->clientError()) {
            $error = ApiError::fromArray(is_array($body) ? Value::object($body, 'Error') : null);
            $message = "Codeweavers {$endpoint->value} returned HTTP {$response->status()}".($error ? ': '.$error->message() : '.');

            throw in_array($response->status(), [401, 403], true)
                ? new CodeweaversAuthenticationException($message, $endpoint, $error, $response->status())
                : new CodeweaversRequestException($message, $endpoint, $error, $response->status());
        }

        if (! is_array($body)) {
            throw new CodeweaversInvalidResponseException("Codeweavers {$endpoint->value} returned a response that is not JSON.", $endpoint);
        }

        if (Value::bool($body, 'HasError')) {
            $error = ApiError::fromResult($body);

            throw new CodeweaversRequestException("Codeweavers {$endpoint->value} failed: ".$error?->message(), $endpoint, $error, $response->status());
        }

        return $body;
    }

    private function log(Endpoint $endpoint, int $status, float $startedAt): void
    {
        if (! data_get($this->config, 'logging.enabled', false)) {
            return;
        }

        Log::channel(data_get($this->config, 'logging.channel'))->info('Codeweavers request', [
            'endpoint' => $endpoint->value,
            'status' => $status,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }
}
