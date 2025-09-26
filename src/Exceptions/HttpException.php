<?php

declare(strict_types=1);

namespace Tommy8699\SuperFaktura\Core\Exceptions;

class HttpException extends \RuntimeException
{
    public function __construct(string $message, public readonly int $statusCode = 0)
    {
        parent::__construct($message, $statusCode);
    }
}
