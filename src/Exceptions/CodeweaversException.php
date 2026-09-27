<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Exceptions;

use NorthBees\CodeweaversApi\Enums\Endpoint;
use RuntimeException;
use Throwable;

/**
 * Base exception for every error raised by the Codeweavers SDK. Messages never contain credentials.
 */
class CodeweaversException extends RuntimeException
{
    public function __construct(
        string $message = '',
        public readonly ?Endpoint $endpoint = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
