<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Throwable;

final class ResponseApiException extends Exception
{
    public function __construct(
        string $message = 'Response API Error',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
