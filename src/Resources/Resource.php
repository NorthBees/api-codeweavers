<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Resources;

use NorthBees\CodeweaversApi\Codeweavers;
use NorthBees\CodeweaversApi\Enums\Endpoint;
use NorthBees\CodeweaversApi\Http\HttpTransport;

/**
 * A group of Codeweavers endpoints.
 */
abstract class Resource
{
    public function __construct(
        protected readonly Codeweavers $codeweavers,
        protected readonly HttpTransport $transport,
    ) {}

    /**
     * @param  array<array-key, mixed>|null  $payload
     * @return array<string, mixed>
     */
    protected function send(Endpoint $endpoint, ?array $payload = null, string ...$pathParameters): array
    {
        return $this->transport->send(
            $endpoint,
            $this->codeweavers->baseUrl(),
            $this->codeweavers->credentials()->apiKey,
            $payload,
            ...$pathParameters,
        );
    }
}
