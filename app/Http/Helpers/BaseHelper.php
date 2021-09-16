<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


class BaseHelper
{
    protected $errors = [];

    public static function onProduction(): bool
    {
        return request()->getHost() === 'welcome.test-correct.nl';
    }

    public function addError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    public function addErrors($errors)
    {
        $this->errors = array_merge($this->errors, $errors);
        return $this;
    }

    public function hasError()
    {
        return (bool) count($this->errors);
    }

    public static function notProduction()
    {
        return str_contains(config('app.url_login'),'testportal') && str_contains(config('app.url_login'),'.test');
    }

    public static function notOnLocal()
    {
        return !(str_contains(config('app.url_login'),'testportal') && (str_ends_with(config('app.url_login'),'.test') || str_ends_with(config('app.url_login'),'.test/')));
    }

    public static function isRunningTestRefreshDb() {
        if(app()->runningInConsole()) {
            // we are running in the console
            $argv = \Request::server('argv', null);
            if(!is_null($argv)&&$argv[0] == 'artisan' && \Illuminate\Support\Str::contains($argv[1],'refreshdb')) {
                return true;
            }
        }
        return false;
    }
}
