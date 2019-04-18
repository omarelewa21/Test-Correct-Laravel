<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{


    protected $baseUrl = 'http://test-correct.test';

    const USER_TEACHER = 'p.vries@31.com';
    const USER_ACCOUNTMANAGER = 'fioretti+schoolbeheerder@test-correct.nl';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        return $app;
    }

    public static function getAuthRequestData($overrides = [])
    {

        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();
        if(!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => static::USER_TEACHER,
        ], $overrides);
    }

    public static function getAuthRequestDataForAccountManager($overrides = [])
    {

        $user = \tcCore\User::where('username','=',static::USER_ACCOUNTMANAGER)->get()->first();
        if(!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => static::USER_ACCOUNTMANAGER,
        ], $overrides);
    }

}
