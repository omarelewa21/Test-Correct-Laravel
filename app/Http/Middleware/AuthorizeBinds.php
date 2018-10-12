<?php namespace tcCore\Http\Middleware;

use Closure;
use tcCore\Lib\Models\AccessCheckable;

class AuthorizeBinds {


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $parameters = $request->route()->parameters();
        foreach($parameters as $parameterName => $parameter) {
            if ($parameter instanceof AccessCheckable && !$parameter->canAccessBoundResource($request, $next)) {
                return $parameter->getAccessDeniedResponse($request, $next);
            }
        }

        return $next($request);
    }

}
