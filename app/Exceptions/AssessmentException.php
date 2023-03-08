<?php

namespace tcCore\Exceptions;

use Exception;
use Throwable;

class AssessmentException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}