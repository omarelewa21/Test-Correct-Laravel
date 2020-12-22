<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 19/01/2019
 * Time: 18:14
 */

namespace tcCore\Exceptions;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use tcCore\Jobs\SendExceptionMail;

class RouteModelBindingNotFoundHttpException extends NotFoundHttpException
{
    /**
     * @param string     $message  The internal exception message
     * @param \Throwable $previous The previous exception
     * @param int        $code     The internal exception code
     */
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        // we throw an exception to bugsnag in order to match the error towards the cake environment
        Bugsnag::notifyException(new \Exception($message));
        parent::__construct(404, $message, $previous, $headers, $code);
    }

}