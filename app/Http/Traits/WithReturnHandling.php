<?php

namespace tcCore\Http\Traits;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use tcCore\Http\Helpers\CakeRedirectHelper;

trait WithReturnHandling
{
    protected $queryStringWithReturnHandling = ['referrer' => ['except' => '']];
    public $referrer; //['type', 'page']

    protected function redirectUsingReferrer()
    {
        if (blank($this->referrer) || blank($this->referrer['page'])) {
            return CakeRedirectHelper::redirectToCake();
        }

        if ($this->referrer['type'] === 'cake') {
            $routeName = CakeRedirectHelper::getRouteNameByUrl($this->referrer['page']);
            if ($routeName) {
                return CakeRedirectHelper::redirectToCake($routeName);
            }
            Bugsnag::notifyException(
                new \Exception(
                    sprintf(
                        'No route name found for referrer page `%s` in file %s line %d',
                        $this->referrer['page'],
                        __FILE__,
                        __LINE__
                    )
                )
            );
        }

        if ($this->referrer['type'] === 'laravel') {
            return redirect($this->referrer['page']);
        }

        return CakeRedirectHelper::redirectToCake();
    }
}