<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

use DateTimeInterface;

final readonly class Registration
{
    public function __construct(
        public ?string $number = null,
        public ?DateTimeInterface $registeredAt = null,
        public string $countryCode = 'GB',
    ) {}

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'RegistrationNumber' => $this->number,
            'DateRegisteredWithDvla' => $this->registeredAt?->format('Y-m-d'),
            'CountryCode' => $this->countryCode,
        ];
    }
}
