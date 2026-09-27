<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data\Finance;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * The finance product a quote was calculated with.
 */
final readonly class Product
{
    public function __construct(
        public ?string $key,
        public ?string $type,
        public ?string $name,
        public ?string $termName,
        public ?string $lender,
        public ?string $lenderLogo,
        public ?string $familyKey,
        public bool $isResidualBased,
        public bool $hasApr,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            key: Value::string($data, 'Key'),
            type: Value::string($data, 'Type'),
            name: Value::string($data, 'Name'),
            termName: Value::string($data, 'TermName'),
            lender: Value::string($data, 'Lender'),
            lenderLogo: Value::string($data, 'LenderLogo'),
            familyKey: Value::string($data, 'FamilyKey'),
            isResidualBased: Value::bool($data, 'IsResidualBased'),
            hasApr: Value::bool($data, 'HasApr'),
        );
    }
}
