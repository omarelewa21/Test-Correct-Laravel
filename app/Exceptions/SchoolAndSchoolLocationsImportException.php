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

class SchoolAndSchoolLocationsImportException extends \Exception
{
    protected $details;

    public function __construct($message, $code = 0, \Throwable $previous = null, $details = [], $addRequestToDetails = true)
    {
        parent::__construct($message,$code,$previous);
        $this->details = (!$addRequestToDetails) ? $details : array_merge($details,request()->all());
    }

    public function getDetails(){
        return $this->details;
    }

    public function sendExceptionMail(){
        try {
            $e = ($this->getPrevious()) ?: $this;
            dispatch(
                new SendExceptionMail($e->getMessage(), $e->getFile(), $e->getLine(), $this->getDetails())
            );
        } catch (\Throwable $th) {
            Bugsnag::notifyException($th);
        }

    }
}