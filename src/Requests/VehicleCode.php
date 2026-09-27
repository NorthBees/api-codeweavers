<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use NorthBees\CodeweaversApi\Enums\IdentifierType;

final readonly class VehicleCode
{
    public function __construct(
        public IdentifierType $type,
        public string $value,
    ) {}

    /**
     * @return array{Type: string, Value: string}
     */
    public function toArray(): array
    {
        return ['Type' => $this->type->value, 'Value' => $this->value];
    }
}
