<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Router;
use tcCore\Lib\User\Roles;

class Authorize {


    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Routes in router
     *
     * @var Guard
     */
    protected $routes;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     */
    public function __construct(Guard $auth, Router $router)
    {
        $this->auth = $auth;
        $this->routes = $router->getRoutes()->getRoutes();
    }

    /**
     * Gets the roles parameter from the route
     *
     * @param $request
     * @return mixed Array with roles or null
     */
    public function getRouteRoles($request) {
        $routeName = $request->route()->getName();

        // If this route has no name, try to find it based on the action it links to (laravel does not give PATCH requests a name when using Route::resource; is annoying).
        if (!$routeName) {
            $routeAction = $request->route()->getActionName();
            foreach($this->routes as $route) {
                if ($route->getActionName() === $routeAction && $route->getName() !== null) {
                    $routeName = $route->getName();
                    break;
                }
            }
        }

        if ($routeName) {
            $routePermission = config('routeRoles');
            if (array_key_exists($routeName, $routePermission)) {
                return $routePermission[$routeName];
            }

            if (array_key_exists('*', $routePermission)) {
                $routeRoles = $routePermission['*'];
            } else {
                $routeRoles = null;
            }

            $routeName = explode('.', $routeName);
            $routeNamePart = null;
            foreach($routeName as $part) {
                if ($routeNamePart !== null) {
                    $routeNamePart .= '.';
                }

                $routeNamePart .= $part;
                if (array_key_exists($routeNamePart.'.*', $routePermission)) {
                    $routeRoles = $routePermission[$routeNamePart.'.*'];
                }
            }

            return $this->arrayToLower($routeRoles);
        }

        return null;
    }

    public function getUserRoles() {
        $user = $this->auth->user();

        if ($user !== null) {
            return $this->arrayToLower(Roles::getUserRoles($user));
        }

        return null;
    }

    private function arrayToLower($array) {
        if ($array === null) {
            return null;
        }

        if (is_string($array)) {
            return strtolower($array);
        }

        array_walk_recursive($array, function(&$item, $key) {
            if (is_string($item)) {
                $item = strtolower($item);
            }
        });

        return $array;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeRoles = $this->getRouteRoles($request);
        $userRoles = $this->getUserRoles();

        if (!$this->hasRoles($routeRoles, $userRoles))
        {
            return response('Unauthorized.', 403);
        }

        return $next($request);
    }

    public function hasRoles($routeRoles, $userRoles) {
        if (is_array($routeRoles)) {
            foreach($routeRoles as $key => $roles) {
                if (strtolower($key) === 'or') {
                    $hasRole = false;

                    foreach($roles as $k => $role) {
                        if (strtolower($k) === 'or') {
                            if (is_array($role) && $this->hasRoles(['or' => $role], $userRoles)) {
                                $hasRole = true;
                            } elseif(in_array($role, $userRoles)) {
                                $hasRole = true;
                            }
                        } elseif (in_array($role, $userRoles)) {
                            $hasRole = true;
                        }
                    }

                    if (!$hasRole) {
                        return false;
                    }
                } elseif(is_array($roles) && !$this->hasRoles($roles, $userRoles)) {
                    return false;
                } elseif (!is_array($roles) && !in_array($roles, $userRoles)) {
                    return false;
                }
            }
            return true;
        } elseif($routeRoles === null) {
            return true;
        } else {
            return in_array($routeRoles, $userRoles);
        }
    }

}
