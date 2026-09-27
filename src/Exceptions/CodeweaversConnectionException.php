<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Exceptions;

/**
 * The API could not be reached, or returned a 5xx response after retries.
 */
class CodeweaversConnectionException extends CodeweaversException {}
