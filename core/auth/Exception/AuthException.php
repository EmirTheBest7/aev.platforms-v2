<?php

declare(strict_types=1);

namespace Core\Auth\Exception;

use RuntimeException;

/**
 * Base type for every exception thrown by the authentication module.
 *
 * Callers (e.g. api/v2/auth/*.php) can catch this single type to handle
 * "any auth failure" generically, or catch the specific subclasses below
 * for precise HTTP status mapping.
 */
abstract class AuthException extends RuntimeException
{
}
