<?php namespace tcCore\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use tcCore\Jobs\SendExceptionMail;
use Throwable;

class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable $e
     * @return void
     */
    public function report(Throwable $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Throwable $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        if($e instanceof DeploymentMaintenanceException) {
            if ($request->expectsJson()) {
                return response()->json(['error' => strip_tags($e->getMessage())], 503);
            } else {
                return response()
                    ->view('errors.deployment-maintenance', ['deployment' => $e->deployment], 503);
            }
        } else if($e instanceof LivewireTestTakeClosedException){
            return response()->make('Test taken away', 406);
        } else if ($this->isHttpException($e)) {
            return $this->renderHttpException($e);
        } else if ($e instanceof QuestionException) {
            dispatch(
                new SendExceptionMail($e->getMessage(), $e->getFile(), $e->getLine(), $e->getDetails())
            );

            throw new HttpResponseException(new Response($e), 422);
        } else {
            return parent::render($request, $e);
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }

    /**
     * Create a response object from the given validation exception.
     * always return invalid json also when headers are not properly set.
     * This hack/function overload was put here after problems with validation responses giving
     * Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return $this->invalidJson($request, $e);
    }

}
