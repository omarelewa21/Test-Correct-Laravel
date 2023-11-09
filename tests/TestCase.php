<?php

namespace Tests;

use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use tcCore\Console\Kernel;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Question\Factory;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Role;
use tcCore\SchoolClass;
use tcCore\Student;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\User;
use tcCore\UserRole;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    use RefreshDatabase;

    protected static $loadedScenario = false;

    public static $skipRefresh = false;


    protected $loadScenario = false;


    protected function refreshInMemoryDatabase()
    {
        if (!RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh',
                ["--path" => "database/migrations/sqlite/2023_01_11_100000_create_table.php"]
            );

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;

            ScenarioLoader::load($this->loadScenario);
        }

        if (!ScenarioLoader::isLoadedScenario($this->loadScenario) && !is_bool($this->loadScenario)) {
            RefreshDatabaseState::$migrated = false;
            $this->refreshInMemoryDatabase();
        }
        $this->beginDatabaseTransaction();
    }

    protected function refreshTestDatabase()
    {
        if (static::$skipRefresh) {
            if (!ScenarioLoader::isLoadedScenario($this->loadScenario)) {
                ScenarioLoader::load($this->loadScenario);
            }
        } else {
            if (!RefreshDatabaseState::$migrated) {
                $this->artisan('migrate:fresh',
                    ["--path" => "database/migrations/sqlite/2023_01_11_100000_create_table.php"]
                );
                logger('migrate:fresh');
                $this->app[Kernel::class]->setArtisan(null);

                RefreshDatabaseState::$migrated = true;

                ScenarioLoader::load($this->loadScenario);
                logger('we initialized the database with the correct scenario');
            }

            if (!ScenarioLoader::isLoadedScenario($this->loadScenario) && !is_bool($this->loadScenario)) {
                RefreshDatabaseState::$migrated = false;
                $this->refreshTestDatabase();
                logger('scenario changed so we have refreshed the database;');
            }
        }

        $this->beginDatabaseTransaction();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        //prevent static property values from leaking between tests
        QuestionGatherer::invalidateAllCache();
        AnswerChecker::invalidateAllCache();
    }

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
    const USER_SCHOOLBEHEERDER_LOCATION1 = 'opensourceschoollocatie1schoolbeheerder@test-correct.nl';
    const USER_ADMIN = 'testadmin@teachandlearncompany.com';

    const USER_STUDENT_ONE = 's1@test-correct.nl';
    const USER_STUDENT_TWO = 's2@test-correct.nl';

    /**
     * @return bool
     */
    public static function isSetUpRun(): bool
    {
        return self::$setUpRun;
    }

    public function login(User $user)
    {
        $this->actingAs($user);
        session()->put('session_hash', $user->session_hash);
    }

    public static function getTeacherOne()
    {
        return User::where('username', '=', static::USER_TEACHER)->first();
    }

    public static function getTeacherTwo(): User
    {
        return User::where('username', '=', static::USER_TEACHER_TWO)->first();
    }

    public static function getStudentOne()
    {
        return User::where('username', '=', static::USER_STUDENT_ONE)->first();
    }

    public static function getStudentTwo()
    {
        return User::where('username', '=', static::USER_STUDENT_TWO)->first();
    }

    public static function getAuthRequestData($overrides = [], User $user = null)
    {
        if (!$user) {
            $user = User::where('username', '=', static::USER_TEACHER)->first();
        }
        if (!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }
        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => $user->username,
        ], $overrides);
    }

    public static function getAuthRequestDataForAccountManager($overrides = [])
    {
        $user = User::where('username', '=', static::USER_ACCOUNTMANAGER)->first();
        if (!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => static::USER_ACCOUNTMANAGER,
        ], $overrides);
    }

    public static function AuthBeheerderGetRequest($url, $params = [])
    {
        $user = User::where('username', '=', static::USER_BEHEERDER)->get()->first();
        if (!$user->session_hash) {
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

    public static function AuthBeheerderGetRequestLocation3($url, $params = [])
    {
        $user = User::where('username', '=', static::USER_SCHOOLBEHEERDER)->first();
        ActingAsHelper::getInstance()->setUser($user);
        if (!$user->session_hash) {
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

    public static function AuthBeheerderGetRequestLocation1($url, $params = [])
    {
        $user = User::where('username', '=', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        ActingAsHelper::getInstance()->setUser($user);
        if (!$user->session_hash) {
            $user->session_hash = $user->generateSessionHash();
            $user->save();
        }

        return sprintf(
            '%s/?session_hash=%s&signature=aaebbf4a062594c979128ec2f2ef477d4f7d08893c6940cc736b62b106f6498f&user=%s&%s',
            $url,
            $user->session_hash,
            static::USER_SCHOOLBEHEERDER_LOCATION1,
            http_build_query($params, '', '&')
        );
    }

    public static function getBeheerderAuthRequestData($overrides = [])
    {
        $user = User::where('username', '=', static::USER_BEHEERDER)->first();
        if (!$user->session_hash) {
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
            User::where('username', '=', static::USER_SCHOOLBEHEERDER)->first(),
            $overrides
        );
    }

    public static function AuthSchoolBeheerderGetRequest($url, $params = [])
    {

        $user = User::where('username', '=', static::USER_SCHOOLBEHEERDER)->first();
        if (!$user->session_hash) {
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
            User::where('username', self::USER_STUDENT_ONE)->first(),
            $overrides
        );
    }

    public static function getStudentXAuthRequestData($overrides = [], $studentNumber = null)
    {
        if ($studentNumber === null) {
            throw new \ErrorException('studentNumber is required;');
        }
        $username = sprintf('s%d@test-correct.nl', $studentNumber);
        $user = User::where('username', $username)->first();
        ActingAsHelper::getInstance()->setUser($user);
        return self::getUserAuthRequestData(
            $user,
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

    public static function authStudentOneGetRequest($url, $params = [])
    {

        return self::authUserGetRequest(
            $url,
            $params,
            User::where('username', 's1@test-correct.nl')->first()
        );
    }

    public static function authTeacherOneGetRequest($url, $params = [])
    {

        return self::authUserGetRequest(
            $url,
            $params,
            User::where('username', 'd1@test-correct.nl')->first()
        );
    }

    public static function authTeacherTwoGetRequest($url, $params = [])
    {
        return self::authUserGetRequest(
            $url,
            $params,
            User::where('username', static::USER_TEACHER_TWO)->first()
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
            'api-c/%s/?session_hash=%s&signature=%s&user=%s&%s',
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
    protected static function getUserAuthRequestData($user, $overrides = [])
    {
        return array_merge([
            'session_hash' => $user->session_hash,
            'user'         => $user->username,
        ], $overrides);
    }

    protected function getUrlWithAuthCredentials($url, $data)
    {
        $startkey = '?';
        if (substr_count($url, '?')) {
            $startkey = '&';
        }
        return sprintf('%s%suser=%s&session_hash=%s', $url, $startkey, $data['user'], $data['session_hash']);
    }

//    protected function setUp(): void
//    {
//        global $argv;
//
//        parent::setUp(); // TODO: Change the autogenerated stub


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
//    }

    protected function toetsActiveren($id)
    {
        $this->updateTestTakeStatus($id, 3);
    }

    protected function toetsInleveren($id)
    {

        $this->updateTestTakeStatus($id, 9);
    }

    private function updateTestTakeStatus($testTakeId, $status)
    {
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

    protected function deleteUser($student)
    {
        User::find($student->getKey())->delete();
    }

    protected function getSchoolBeheerder()
    {
        $user = User::where('username', static::USER_SCHOOLBEHEERDER)->first();
        $this->actingAs($user);
        return $user;
    }

    protected function createStudent($password, $schoolLocation, $schoolClass = null, $nr = null)
    {
        if ($nr === null) {
            throw new \ErrorException('parameter $nr is required');
        }
        $user = User::create([
            'school_location_id' => $schoolLocation->getKey(),
            'username'           => sprintf('info+%s-%d@test-correct.nl', $schoolLocation->name, $nr),
            'password'           => \WirisHash::make($password),
            'name_first'         => $schoolLocation->name,
            'name'               => sprintf('student-%d', $nr),
            'api_key'            => sha1(time()),
            'send_welcome_email' => 1
        ]);


        $schoolClass = $this->setSchoolClassIfNull($schoolClass, $schoolLocation);

        if (!$user) {
            throw new \Exception('could not create student');
        }

        UserRole::create([
            'user_id' => $user->getKey(),
            'role_id' => 3
        ]);

        Student::create([
            'user_id'  => $user->getKey(),
            'class_id' => $schoolClass->getKey(),
        ]);

        return $user;
    }

    private function setSchoolClassIfNull($schoolClass, $schoolLocation)
    {
        if (null == $schoolClass) {
            $schoolClass = SchoolClass::create([
                'school_location_id'              => $schoolLocation->getKey(),
                'education_level_id'              => 12,
                'school_year_id'                  => $schoolLocation->schoolLocationSchoolYears->first()->school_year_id,
                'name'                            => sprintf('%s klas', $schoolLocation->name),
                'education_level_year'            => 2,
                'is_main_school_class'            => 1,
                'do_not_overwrite_from_interface' => 0,
            ]);
        }
        return $schoolClass;
    }

    protected function createTeacherFromUser(User $user, $schoolLocation, $schoolClass = null)
    {

        if (!$user) {
            throw new \Exception('could not create teacher');
        }

        $schoolClass = $this->setSchoolClassIfNull($schoolClass, $schoolLocation);

        Teacher::create([
            'user_id'    => $user->getKey(),
            'class_id'   => $schoolClass->getKey(),
            'subject_id' => 30
        ]);

        return $user->refresh();
    }

    protected function createTeacher($password, $schoolLocation, $schoolClass = null)
    {
        $user = User::create([
            'school_location_id' => $schoolLocation->getKey(),
            'username'           => sprintf('info+%s-teacher@test-correct.nl', $schoolLocation->name),
            'password'           => \WirisHash::make($password),
            'name_first'         => $schoolLocation->name,
            'name'               => sprintf('teacher'),
            'api_key'            => sha1(time()),
            'send_welcome_email' => 1,
            'user_roles'         => [Role::TEACHER],
        ]);

        return $this->createTeacherFromUser($user, $schoolLocation, $schoolClass);
    }

    public function createOpenQuestion(array $attributes = [])
    {
        $defaultAttributes = [
            'subject_id'                => 1,
            'education_level_id'        => 1,
            'question'                  => '<p>hoi</p>',
            'education_level_year'      => '1',
            'score'                     => '1',
            'owner_id'                  => '1',
            'decimal_score'             => '0',
            'note_type'                 => 'NONE',
            'add_to_database'           => '1',
            'is_subquestion'            => '0',
            'is_open_source_content'    => '1',
            'closeable'                 => '0',
            'html_specialchars_encoded' => '0',
            'all_or_nothing'            => '0',
            'fix_order'                 => '0',
            'answer'                    => '<p>doei</p>'
        ];
        $attributes = array_merge($defaultAttributes, $attributes);

        $question = Factory::makeQuestion('OpenQuestion');
        $question->fill($attributes);
        $question->save();
        return $question;
    }

    /**
     * Call a private or protected method on an object that is normally unreachable from within a unit test
     */
    public function callPrivateMethod(object $object, string $methodName, array $arguments = [])
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $arguments);
    }
}
