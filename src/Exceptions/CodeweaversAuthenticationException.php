<?php

declare(strict_types=1);

namespace NorthBees\CodeweaversApi\Exceptions;

/**
 * The API key was rejected (HTTP 401 or 403).
 */
class CodeweaversAuthenticationException extends CodeweaversRequestException {}
