<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Data;

use NorthBees\CodeweaversApi\Support\Value;

/**
 * An error object as returned at the root, per vehicle or per quotation.
 */
final readonly class ApiError
{
    public function __construct(
        public ?string $userMessage,
        public ?string $technicalMessage,
        public ?string $code,
    ) {}

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if ($data === null || $data === []) {
            return null;
        }

        return new self(
            userMessage: Value::string($data, 'UserMessage'),
            technicalMessage: Value::string($data, 'TechnicalMessage'),
            code: Value::string($data, 'Code'),
        );
    }

    /**
     * An error for an object with a HasError flag, or null when it has none.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromResult(array $data): ?self
    {
        $error = self::fromArray(Value::object($data, 'Error'));

        if ($error === null && Value::bool($data, 'HasError')) {
            return new self(null, null, null);
        }

        return $error;
    }

    public function message(): string
    {
        return $this->userMessage ?? $this->technicalMessage ?? 'Codeweavers returned an error.';
    }
}
