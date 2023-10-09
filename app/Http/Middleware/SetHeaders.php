<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $handle = $next($request);
        if (method_exists($handle, 'withHeaders')) { // don't do this for instance for Binary File Responses
            $handle->headers->set('X-Frame-Options', 'SAMEORIGIN');
            if (config('custom.enable_hsts') && config('app.env') == 'production') {
                $handle->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubdomains');
            }
        }


        return $handle;
    }
}
