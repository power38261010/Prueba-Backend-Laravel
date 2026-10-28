<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected int $statusCode;

    public function __construct(
        string $message = '',
        int $statusCode = 400,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
