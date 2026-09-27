<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Exceptions;

use NorthBees\CodeweaversApi\Data\ApiError;
use NorthBees\CodeweaversApi\Enums\Endpoint;

/**
 * The API rejected the request (4xx) or reported a top-level error (HasError).
 */
class CodeweaversRequestException extends CodeweaversException
{
    public function __construct(
        string $message,
        ?Endpoint $endpoint = null,
        public readonly ?ApiError $error = null,
        public readonly ?int $status = null,
    ) {
        parent::__construct($message, $endpoint, $status ?? 0);
    }
}
