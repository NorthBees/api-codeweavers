<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Enums;

enum Environment: string
{
    case Sandbox = 'sandbox';
    case Production = 'production';

    /**
     * The default base URL, used when the config does not override it.
     */
    public function defaultBaseUrl(): string
    {
        return match ($this) {
            self::Sandbox => 'https://demoservices.codeweavers.net',
            self::Production => 'https://services.codeweavers.net',
        };
    }
}
