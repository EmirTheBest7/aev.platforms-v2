<?php

declare(strict_types=1);

namespace Core\Routing;

/** Thrown by controllers/router to produce a specific public error page. */
final class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $status, string $message = '')
    {
        parent::__construct($message !== '' ? $message : 'HTTP ' . $status, $status);
    }
}
