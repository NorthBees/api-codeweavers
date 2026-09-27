<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Requests;

/**
 * The organisation (dealer) a request is run as.
 */
final readonly class OrganisationIdentifier
{
    public function __construct(
        public string $value,
        public string $type = 'AssociatedDealerKey',
    ) {}

    public static function associatedDealerKey(string $value): self
    {
        return new self($value);
    }

    public function isAssociatedDealerKey(): bool
    {
        return $this->type === 'AssociatedDealerKey';
    }

    /**
     * @return array{Type: string, Value: string}
     */
    public function toArray(): array
    {
        return ['Type' => $this->type, 'Value' => $this->value];
    }
}
