<?php

namespace tcCore\Exceptions;

use Exception;

class LivewireTestTakeClosedException extends Exception
{
    public $instance;
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
