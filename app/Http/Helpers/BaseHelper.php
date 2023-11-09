<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\AppVersionInfo;
use tcCore\FailedLogin;
use tcCore\Jobs\SendInactiveUserMail;
use tcCore\LoginLog;
use tcCore\TemporaryLogin;

class BaseHelper
{
    protected $errors = [];

    public static function getLoginUrl()
    {
        $url = config('app.url_login');
        if(GlobalStateHelper::getInstance()->isOnDeploymentTesting()){
            $url = Str::replaceFirst('portal', 'portal2', $url);
        }
        return $url;
    }

    public static function getLogoutUrl()
    {
        $url = config('app.url_logout');
        if(GlobalStateHelper::getInstance()->isOnDeploymentTesting()){
            $url = Str::replaceFirst('portal', 'portal2', $url);
        }
        return $url;
    }

    public static function doLoginProcedure()
    {
        $user = auth()->user();
        if(!session('TLCHeader')){
            AppVersionDetector::handleHeaderCheck();
        }

        $sessionHash = $user->generateSessionHash();
        $user->setSessionHash($sessionHash);

        UserHelper::setSystemLanguage($user);
        UserHelper::setAdditionalUserAttributes($user);
        UserHelper::handleTeacherEnvironment($user);

        LoginLog::create(['user_id' => $user->getKey()]);
        if ($user->isA('teacher')){
            DB::table('mails_send')
                ->where('user_id', $user->getKey())
                ->where('mailable',SendInactiveUserMail::class)
                ->delete();
        }
        AppVersionInfo::createFromSession();
        FailedLogin::solveForUsernameAndIp($user->username, request()->ip());
    }

    public static function getCurrentVersion(): string
    {
        $file = base_path('version.txt');
        if(file_exists($file)){
            return file_get_contents($file);
        }
        return '-';
    }

    public static function onProduction(): bool
    {
        return request()->getHost() === 'welcome.test-correct.nl';
    }

    public static function getLivewireOriginalPath($request)
    {
        return json_decode($request->getContent())->fingerprint->path;
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

    public static function inNgrokEnvironment()
    {
        return str_contains(env('URL_LOGIN'),'ngrok.io');
    }

    public static function notOnLocal()
    {
        return !(str_contains(config('app.url_login'),'testportal') && (str_ends_with(config('app.url_login'),'.test') || str_ends_with(config('app.url_login'),'.test/')));
    }

        public static function onLocal()
    {
        return str_contains(config('app.url_login'),'testportal') && (str_ends_with(config('app.url_login'),'.test') || str_ends_with(config('app.url_login'),'.test/'));
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

    public static function createRedirectUrlWithTemporaryLoginUuid($uuid, $redirectUrl, $returnUrl = false)
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
        if($returnUrl){
            return $response->url;
        }
        return  response()->json($response);
    }

    public static function getMaxFileUploadSize()
    {
        return BaseHelper::returnBytes(ini_get('upload_max_filesize'));
    }

    private static function returnBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = Str::substr($value, 0, -1);
        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        return $value;
    }

    public static function transformHtmlChars($answer)
    {
        $answer = str_replace('<','&lt;',$answer);
        $answer = str_replace('>','&gt;',$answer);
        $answer = str_replace('&;','&amp;',$answer);
        return $answer;
    }

    public static function transformHtmlCharsReverse($answer, $doHtmlEntities = true)
    {
        $answer = str_replace('&lt;','<',$answer);
        $answer = str_replace('&gt;','>',$answer);
        $answer = str_replace('&amp;','&',$answer);
        if($doHtmlEntities) {
            $answer = htmlentities($answer, null, 'utf-8');
        }
        $answer = str_replace("&nbsp;", ' ', $answer);

        return $answer;
    }

    public static function returnOnlyRegularAlphaNumeric($string, $allowedExtraCharsExpression = null)
    {
        if(is_string($string)) {
            $string = strip_tags($string);
            $charsExpression = 'a-zA-Z0-9 \-_' . $allowedExtraCharsExpression ?? '';
            return preg_replace("/[^" . $charsExpression . "]+/", "", $string);
        }
        return $string;
    }

    public static function getLoginUrlWithOptionalMessage($message = null, $isError = false)
    {
        $queryAr = [];
        if($message){
            $type = ($isError) ? 'entree_error_message' : 'message';
            $queryAr[$type] = $message;
        }
        return route('auth.login',$queryAr);
    }

    public static function getWorkableTypeFromUploadMime($mime)
    {
        if (str($mime)->startsWith('application')) {
            if (str(explode('/', $mime)[1])->contains('word')) {
                return 'word';
            }

            return 'pdf';
        }

        return collect(explode('/', $mime))->first();
    }

    public static function browserLanguage(): string
    {
        $lang = 'nl';
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            $lang = $language === 'en' ? 'en' : 'nl';
        }

        return $lang;
    }
}
