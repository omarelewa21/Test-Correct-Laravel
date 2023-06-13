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
            $uuid = $this->extractUuidFromUrl($this->referrer['page']);
            $routeName = CakeRedirectHelper::getRouteNameByUrl($this->referrer['page'], uuid: $uuid);
            if ($routeName) {
                return CakeRedirectHelper::redirectToCake($routeName, uuid: $uuid);
            }
            $this->notifyBugsnag();
        }

        if ($this->referrer['type'] === 'laravel') {
            return redirect($this->referrer['page']);
        }

        return CakeRedirectHelper::redirectToCake();
    }

    private function notifyBugsnag(): void
    {
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

    private function extractUuidFromUrl($url): ?string
    {
        $extract_guid_pattern = "/(?:\\{{0,1}(?:[0-9a-fA-F]){8}-(?:[0-9a-fA-F]){4}-(?:[0-9a-fA-F]){4}-(?:[0-9a-fA-F]){4}-(?:[0-9a-fA-F]){12}\\}{0,1})/";
        preg_match($extract_guid_pattern, $url, $matches);
        return $matches[0] ?? null;
    }
}