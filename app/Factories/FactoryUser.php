<?php

namespace tcCore\Factories;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\User\Factory;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\User;

class FactoryUser
{
    use DoWhileLoggedInTrait;
    use RandomCharactersGeneratable;

    public static $teacherIterator = [];
    public static $studentIterator = [];
    public $user;
    protected $userProperties = [];
    protected $teacherInfo = [];

    public static function createAccountManager(string $schoolName = null, array $userProperties = []): FactoryUser
    {
        $factory = new static;

        if ($schoolName) {
            $username = 'AM+' . $schoolName . '@factory.test';
        } else {
            $username = 'AM+' . $factory->randomCharacters(4) . '@factory.test';
        }
        $factory->userProperties = array_merge($factory->definition(), [
            'name_first'   => 'Account',
            'name'         => 'Manager',
            'abbreviation' => 'AM',
            'username'     => $username,
            'user_roles'   => [5], //account manager role == 5
        ], $userProperties);

        $factory->createUser();


        return $factory;
    }

    public static function createSchoolManager(SchoolLocation $schoolLocation, array $userProperties = [])
    {
        $factory = new static;

        $username = 'SchoolManager-' . $schoolLocation->name . '@factory.test';

        $factory->userProperties = array_merge($factory->definition(), [
            'name_first'         => 'FactorySchool',
            'name'               => 'SchoolManager',
            'abbreviation'       => 'SM',
            'username'           => $username,
            'school_location_id' => $schoolLocation->getKey(),
            'user_roles'         => [Role::SCHOOLMANAGER],
        ], $userProperties);

        $factory->createUser();

        return $factory;
    }

    public static function createTeacher(SchoolLocation $schoolLocation, bool $numericName = true, array $userProperties = []): FactoryUser
    {
        $factory = new static;

        $schoolLocationId = $schoolLocation->getKey();
        if (!isset(static::$teacherIterator[$schoolLocationId])) {
            static::$teacherIterator[$schoolLocationId] = 0;
        }
        $number = self::getCountTeachersForCurrentSchool($schoolLocation) + 1;

        if (!$numericName) {
            $factory->createTeacherInfo($number, $schoolLocation);
        }

        $factory->userProperties = array_merge($factory->definition(), [
            'name_first'         => 'Teacher',
            'name'               => 'Teacher ' . $number,
            'abbreviation'       => 'T' . $number,
            'school_location_id' => $schoolLocationId,
            'username'           => $schoolLocation->name . '_Teacher' . $number . '@factory.test',
            'user_roles'         => [1], //teacher role == 1
        ], $factory->teacherInfo, $userProperties);

        $schoolManager = $schoolLocation->users()->whereIn('users.id', function ($query) {
            $query->from('user_roles')->where('user_roles.role_id', 6)->select('user_roles.user_id');
        })->first();

        $factory->doWhileLoggedIn(function () use ($factory) {
            $factory->createUser();
        }, $schoolManager);

        return $factory;
    }

    /**
     * If asked for a non-numeric teacher name,
     * create a teacher info with a fake Dutch name
     */
    protected function createTeacherInfo($number, $schoolLocation)
    {
        $faker = \Faker\Factory::create('nl_NL');

        $firstName = $faker->firstName();

        $this->teacherInfo = [
            'name_first'   => $firstName,
            'name'         => 'Docent ' . $firstName,
            'abbreviation' => 'D' . $number,
            'username'     => $schoolLocation->name . '_Docent' . $firstName . '@factory.test',
        ];
    }

    public function addSchoolLocation(SchoolLocation $schoolLocation)
    {
        if (!$this->user->isA('teacher')) {
            throw new \Exception("can only add multiple school locations to users with the role Teacher");
        }
        $this->user->addSchoolLocation($schoolLocation);

        return $this;
    }

    private static function getCountStudentsForCurrentSchool(SchoolLocation $schoolLocation)
    {
        return User::join('user_roles', 'users.id', 'user_roles.user_id')
            ->where('school_location_id', $schoolLocation->getKey())
            ->where('user_roles.role_id', 3)
            ->count();
    }

    private static function getCountTeachersForCurrentSchool(SchoolLocation $schoolLocation)
    {
        return User::join('user_roles', 'users.id', 'user_roles.user_id')
            ->join('school_location_user', 'users.id', 'school_location_user.user_id')
            ->where('school_location_user.school_location_id', $schoolLocation->getKey())
            ->where('user_roles.role_id', 1)
            ->count();
    }

    public static function createStudent(SchoolLocation $schoolLocation, array $userProperties = []): FactoryUser
    {
        $factory = new static;

        $schoolLocationId = $schoolLocation->getKey();

        $number = self::getCountStudentsForCurrentSchool($schoolLocation) + 1;

        $factory->userProperties = array_merge($factory->definition(), [
            'name_first'         => 'Student',
            'name'               => 'Student ' . $number,
            'abbreviation'       => 'S' . $number,
            'school_location_id' => $schoolLocationId,
            'username'           => $schoolLocation->name . '_Student' . $number . '@factory.test',
            'user_roles'         => [3], //student role == 3
        ], $userProperties);

        $schoolManager = $schoolLocation->users()->whereIn('users.id', function ($query) {
            $query->from('user_roles')->where('user_roles.role_id', 6)->select('user_roles.user_id');
        })->first();

        $factory->doWhileLoggedIn(function () use ($factory) {
            $factory->createUser();
        }, $schoolManager);

        return $factory;
    }

    protected function createUser(): void
    {
        $userFactory = new Factory(new User());
        $this->user = $userFactory->generate($this->userProperties);
    }

    protected function definition(): array
    {
        return [
            'name_first'         => 'Name',
            'name_suffix'        => '',
            'name'               => 'LastName',
            'abbreviation'       => 'N',
            'school_location_id' => null,
            'school_id'          => null,
            'username'           => 'Name-LastName@factory.test',
            'password'           => 'TCSoBit500',
            'user_roles'         => [1],
            'gender'             => 'Male',
        ];
        //roles:
        //  1 Teacher
        //  3 Student
        //  5 account manager //school/school_location have account manager tcCore\User as user_id in existing database.
        //  6 school manager
    }

    public static function createAdmin(array $userProperties = []): FactoryUser
    {
        $factory = new static;

        $factory->userProperties = array_merge($factory->definition(), [
            'name_first'   => 'Jaap',
            'name'         => 'Admin',
            'abbreviation' => 'ADM',
            'username'     => 'admin@factory.test',
            'user_roles'   => [Role::ADMINISTRATOR],
        ], $userProperties);

        $factory->createUser();

        return $factory;
    }

}
