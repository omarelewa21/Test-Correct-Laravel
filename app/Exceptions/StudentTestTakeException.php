<?php

namespace tcCore\Exceptions;

use Exception;
use Livewire\Exceptions\BypassViewHandler;
use Throwable;

class StudentTestTakeException extends Exception
{
    use BypassViewHandler;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}