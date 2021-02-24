<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use tcCore\Test;
use tcCore\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * If true, setup has run at least once.
     *
     * @var boolean
     */
    protected static $setUpRun = false;

    protected $baseUrl = 'http://test-correct.test';

    const USER_TEACHER = 'd1@test-correct.nl';
    const USER_TEACHER_TWO = 'd2@test-correct.nl';

    const USER_BEHEERDER = 'opensourceschoollocatie1@test-correct.nl';
    const FIORETTI_TEACHER = 'd1@test-correct.nl';
    const USER_ACCOUNTMANAGER = 'standaardschoolbeheerder@test-correct.nl';
    const USER_SCHOOLBEHEERDER = 'standaardschoolbeheerder@test-correct.nl';

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

    public static function AuthBeheerderGetRequest($url, $params=[]) {

        $user = \tcCore\User::where('username','=',static::USER_BEHEERDER)->get()->first();
        if(!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return sprintf(
            '%s/?session_hash=%s&signature=aaebbf4a062594c979128ec2f2ef477d4f7d08893c6940cc736b62b106f6498f&user=%s&%s',
            $url,
            $user->session_hash,
            static::USER_BEHEERDER,
            http_build_query($params, '', '&')
        );
    }

    public static function getBeheerderAuthRequestData($overrides = [])
    {
        $user = \tcCore\User::where('username','=',static::USER_BEHEERDER)->get()->first();
        if(!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => static::USER_BEHEERDER,
        ], $overrides);
    }

    public static function getSchoolBeheerderAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            User::where('username','=',static::USER_SCHOOLBEHEERDER)->get()->first(),
            $overrides
        );
    }

    public static function AuthSchoolBeheerderGetRequest($url, $params=[]) {

        $user = \tcCore\User::where('username','=',static::USER_SCHOOLBEHEERDER)->get()->first();
        if(!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return sprintf(
            '%s/?session_hash=%s&signature=aaebbf4a062594c979128ec2f2ef477d4f7d08893c6940cc736b62b106f6498f&user=%s&%s',
            $url,
            $user->session_hash,
            static::USER_SCHOOLBEHEERDER,
            http_build_query($params, '', '&')
        );
    }

    public static function getStudentOneAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            User::where('username', 's1@test-correct.nl')->first(),
            $overrides
        );
    }

    public static function getStudentXAuthRequestData($overrides = [],$studentNumber)
    {
        $username = sprintf('s%d@test-correct.nl',$studentNumber);
        dump($username);
        return self::getUserAuthRequestData(
            User::where('username', $username)->first(),
            $overrides
        );
    }

    public static function getTeacherOneAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            User::where('username', 'd1@test-correct.nl')->first(),
            $overrides
        );
    }

    public static function getAccountManagerAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            User::where('username', 'accountmanager@test-correct.nl')->first(),
            $overrides
        );
    }

    public static function getRttiSchoolbeheerderAuthRequestData($overrides = [])
    {
        return self::getUserAuthRequestData(
            User::where('username', 'rtti-schoolbeheerder@test-correct.nl')->first(),
            $overrides
        );
    }

    public static function authStudentOneGetRequest($url, $params=[]) {

        return self::authUserGetRequest(
            $url,
            $params,
            User::where('username', 's1@test-correct.nl')->first()
        );
    }

    public static function authTeacherOneGetRequest($url, $params=[]) {

        return self::authUserGetRequest(
            $url,
            $params,
            User::where('username', 'd1@test-correct.nl')->first()
        );
    }

    /**
     * @param $url
     * @param $params
     * @param $user
     */
    public static function authUserGetRequest($url, $params, $user)
    {
        return sprintf(
            '%s/?session_hash=%s&signature=%s&user=%s&%s',
            $url,
            $user->session_hash,
            '58500ec4dc43d4e57fb0c1b1edadc31086cba65cd8c7adc52aa22d569f9a89cf',
            $user->username,
            http_build_query($params, '', '&')
        );
    }

    /**
     * @param $user
     * @param $overrides
     * @return array
     */
    protected static function getUserAuthRequestData($user, $overrides=[])
    {
        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => $user->username,
        ], $overrides);
    }

    protected function getUrlWithAuthCredentials($url,$data){
        $startkey = '?';
        if(substr_count($url,'?')){
            $startkey = '&';
        }
        return sprintf('%s%suser=%s&session_hash=%s',$url,$startkey,$data['user'],$data['session_hash']);
    }

    protected function setUp(): void
    {
        global $argv;

        parent::setUp(); // TODO: Change the autogenerated stub


        // skip refresh db when running phpunit
        // with a 5th parameter ignoredb for performance;
        // example:
        // phpunit UserControllerTest tests/Feature/UserControllerTest.php --filter _ ignoredb
        // underscore as that is a common character in all the tests except for single word tests which we don't have
//        if (!static::$setUpRun) {
//            if (!(array_key_exists(5, $argv) && $argv[5] == 'ignoredb')) {
//                $this->artisan('test:refreshdb');
//                static::$setUpRun = true;
//            }
//        }
    }

    protected function toetsActiveren($id) {
        $this->updateTestTakeStatus($id, 3);
    }

    protected function toetsInleveren($id) {

        $this->updateTestTakeStatus($id, 9);
    }

    private function updateTestTakeStatus($testTakeId, $status) {
        $response = $this->put(
            sprintf(
                'api-c/test_take/%s',
                $testTakeId
            ),
            static::getTeacherOneAuthRequestData(
                ['test_take_status_id' => $status]
            )
        );
        $this->assertEquals(
            $status,
            $response->decodeResponseJson()['test_take_status_id']
        );

        $response->assertStatus(200);
    }

    protected function deleteTest($test)
    {
        Test::findOrFail($test['id'])->delete();
    }

    protected function deleteUser($student){
        User::find($student->getKey())->delete();
    }

    protected function getSchoolBeheerder(){
        $user = User::where('username',static::USER_SCHOOLBEHEERDER)->first();
        return $user;
    }

}
