<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use tcCore\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $baseUrl = 'http://test-correct.test';

    const USER_TEACHER = 'p.vries@31.com';

    const USER_BEHEERDER = 'schoolbeheerder@connected-software.com';
    const USER_BEHEERDER_SESSION_HASH = 'CXLtEIpVFXUrR8QjN9OYS4flMP0j6KFDrIML0Z1LABX3HXPmBBOQpUNGrQHQoELFcd2tLI3gRaXzm2sXonuPypynJpwBao7bP5PW';
    const FIORETTI_TEACHER = 'krs@fioretti.nl';

    public static function getAuthRequestData($overrides = [])
    {
        return array_merge([
            'session_hash' => '5mTzff9qk9TObgv0NfsU7JjLDgnBsRRYSDfASpPEYLb2GjZHSQh1aePy1vhnBq1gFsjWprSU0dsCUgKTJhJVzbIHEEd7Mzw2Y1Y',
            'user'         => static::USER_TEACHER,
        ], $overrides);
    }

    public static function getAuthFiorettiRequestData($overrides = [])
    {
        $user = User::where('username', static::FIORETTI_TEACHER)->first();

        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => $user->username,
        ], $overrides);
    }

    public static function AuthFiorettiRequest($url, $params=[])
    {
        $user = User::where('username', static::FIORETTI_TEACHER)->first();

        return sprintf(
            '%s/?session_hash=%s&signature=aaebbf4a062594c979128ec2f2ef477d4f7d08893c6940cc736b62b106f6498f&user=%s&%s',
            $url,
            $user->session_hash,
            $user->username,
            http_build_query($params, '', '&')
        );
    }

    public static function AuthBeheerderGetRequest($url, $params=[]) {

        return sprintf(
            '%s/?session_hash=%s&signature=aaebbf4a062594c979128ec2f2ef477d4f7d08893c6940cc736b62b106f6498f&user=%s&%s',
            $url,
            static::USER_BEHEERDER_SESSION_HASH,
            static::USER_BEHEERDER,
            http_build_query($params, '', '&')
        );
    }

    public static function getBeheerderAuthRequestData($overrides = [])
    {
        return array_merge([
            'session_hash' => 'CXLtEIpVFXUrR8QjN9OYS4flMP0j6KFDrIML0Z1LABX3HXPmBBOQpUNGrQHQoELFcd2tLI3gRaXzm2sXonuPypynJpwBao7bP5PW',
            'user'         => static::USER_BEHEERDER,
        ], $overrides);
    }



}