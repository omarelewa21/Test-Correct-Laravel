<?php

namespace tcCore\Exceptions;

use Exception;
use tcCore\Deployment;

class CleanRedirectException extends Exception
{

    public $url;

    public function __construct($url)
    {
        $this->url = $url;
    }
}
