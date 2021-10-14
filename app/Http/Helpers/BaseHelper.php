<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Str;
use tcCore\TemporaryLogin;

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

    public static function createRedirectUrlWithTemporaryLoginUuid($uuid, $redirectUrl)
    {
        $response = new \stdClass;

        $relativeUrl = sprintf('%s?redirect=%s',
            route('auth.temporary-login.redirect',[$uuid],false),
            rawurlencode($redirectUrl)
        );
        if(Str::startsWith($relativeUrl,'/')) {
            $relativeUrl = Str::replaceFirst('/', '', $relativeUrl);
        }

        $response->url = sprintf('%s%s',config('app.base_url'), $relativeUrl);

        return  response()->json($response);
    }

    public static function createRedirectUrlWithTemporaryLoginUuidToCake($uuid, $redirectUrl)
    {
        $response = new \stdClass;

        $relativeUrl = sprintf('%s?redirect=%s',
            sprintf('users/temporary_login/%s',$uuid),
            rawurlencode($redirectUrl)
        );
        if(Str::startsWith($relativeUrl,'/')) {
            $relativeUrl = Str::replaceFirst('/', '', $relativeUrl);
        }

        return sprintf('%s%s',config('app.url_login'), $relativeUrl);
    }
}
