<?php namespace tcCore\Lib\Models;

use Closure;

interface AccessCheckable {
    public function canAccessBoundResource($request, Closure $next);
    public function getAccessDeniedResponse($request, Closure $next);
}