<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi;

/**
 * A Codeweavers API key. The key is redacted from dumps.
 */
final readonly class CodeweaversCredentials
{
    public function __construct(
        #[\SensitiveParameter] public string $apiKey,
    ) {}

    /**
     * Build credentials from the package config, or null when the key is blank.
     *
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): ?self
    {
        $apiKey = $config['api_key'] ?? null;

        return is_string($apiKey) && $apiKey !== '' ? new self($apiKey) : null;
    }

    /**
     * @return array{apiKey: string}
     */
    public function __debugInfo(): array
    {
        return ['apiKey' => '********'];
    }
}
