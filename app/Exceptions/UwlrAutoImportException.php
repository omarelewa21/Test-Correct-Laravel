<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 19/01/2019
 * Time: 18:14
 */

namespace tcCore\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use tcCore\Jobs\SendExceptionMail;

class UwlrAutoImportException extends \Exception
{
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message,$code,$previous);
        Bugsnag::notifyException($this);
    }
}