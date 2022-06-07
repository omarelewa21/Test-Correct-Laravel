<?php

namespace Tests\Unit\FactoryTests\SchoolFactory;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactoryUser;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Period;
use tcCore\User;
use Tests\TestCase;

class FactoryUserTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_create_multiple_teacher_with_incrementing_numbers_in_name()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create('S100')->school,
            'SL101'
        )->schoolLocation;

        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, Carbon::today()->format('Y'))
            ->addPeriodFullYear();

        $userFactory1 = FactoryUser::createTeacher($schoolLocation);
        $userFactory2 = FactoryUser::createTeacher($schoolLocation);

        $createdUsers = $schoolLocation->users->filter(function($user) {
            return $user->roles()->get()->contains(1) && $user->demo == 0;
        });

        $this->assertEquals(2, $createdUsers->count());
        $this->assertEquals('Teacher 1', $createdUsers->values()[0]->name);
        $this->assertEquals('T1', $createdUsers->values()[0]->abbreviation);
        $this->assertStringContainsString('Teacher1', $createdUsers->values()[0]->username);
        $this->assertEquals('Teacher 2', $createdUsers->values()[1]->name);
        $this->assertEquals('T2', $createdUsers->values()[1]->abbreviation);
        $this->assertStringContainsString('Teacher2', $createdUsers->values()[1]->username);
    }

    /**
     * You can only create a Teacher, if there is a Period available for the current date/today
     * @test
     */
    public function can_create_user_with_role_Teacher()
    {
        $schoolLocation1 = FactorySchoolLocation::create(
            FactorySchool::create('UserSchool')->school,
            'location1'
        )->schoolLocation;

        $schoolYearFactory1 = FactorySchoolYear::create($schoolLocation1, Carbon::today()->format('Y'))
            ->addPeriodFullYear();

        $userFactory = FactoryUser::createTeacher($schoolLocation1);

        $this->assertTrue(
            $schoolLocation1->users()
                ->where(
                    'id',
                    $userFactory->user->getKey())
                ->exists()
        );
        $this->assertEquals("Teacher", $userFactory->user->roles()->first()->name);
    }

    /** @test */
    public function can_create_user_with_role_teacher_with_non_numeric_dutch_name()
    {
        $schoolLocation1 = FactorySchoolLocation::create(
            FactorySchool::create('UserSchool')->school,
            'location1'
        )->schoolLocation;

        $schoolYearFactory1 = FactorySchoolYear::create($schoolLocation1, Carbon::today()->format('Y'))
            ->addPeriodFullYear();

        $userFactory = FactoryUser::createTeacher($schoolLocation1, false);

        $this->assertNotNull($userFactory->user->username);
        $this->assertFalse(stripos($userFactory->user->username, 'Teacher'));

    }

    /**
     * Teachers are able to belong to multiple school locations, via the school_location_users table
     * Testing the functionality of adding a new teacher to a second school_location
     * @test
     */
    public function can_add_second_school_location_to_user_with_role_Teacher()
    {
        $school = FactorySchool::create('TestSchoolXYZ')->school;
        $schoolLocation1 = FactorySchoolLocation::create($school, 'TestLocationABC')->schoolLocation;
        $schoolLocation2 = FactorySchoolLocation::create($school, 'TestLocationDEF')->schoolLocation;

        //cannot create Teacher without creating period
        $currentYear = Carbon::today()->format('Y'); //"2022"
        $schoolYearFactory1 = FactorySchoolYear::create($schoolLocation1, (int) $currentYear)
            ->addPeriod('Period 1 sl 1', Carbon::today()->startOfYear(), Carbon::today()->endOfYear());
        $schoolYearFactory2 = FactorySchoolYear::create($schoolLocation2, (int) $currentYear)
            ->addPeriod('Period 1 sl 2', Carbon::today()->startOfYear(), Carbon::today()->endOfYear());

        $startCounts = [
            'user'                  => User::count(),
            'school_location_users' => DB::table('school_location_user')->count(),
        ];

        $user = FactoryUser::createTeacher($schoolLocation1);

        $user->addSchoolLocation($schoolLocation2);

        $AmountOfSchoolLocationUsersRecordsCreatedForUser =
            DB::table('school_location_user')
                ->where('user_id', $user->user->getKey())
                ->whereIn('school_location_id', [$schoolLocation1->getKey(), $schoolLocation2->getKey()])
                ->count();
        $this->assertEquals(2, $AmountOfSchoolLocationUsersRecordsCreatedForUser);
        $this->assertEquals($startCounts['user'] + 1, User::count());
    }

    /** @test */
    public function can_create_user_with_role_student()
    {
        $schoolLocation1 = FactorySchoolLocation::create(
            FactorySchool::create('UserSchool')->school,
            'location1'
        )->schoolLocation;

        $schoolYearFactory1 = FactorySchoolYear::create($schoolLocation1, Carbon::today()->format('Y'))
            ->addPeriodFullYear();

        $userFactory = FactoryUser::createStudent($schoolLocation1);

        $this->assertTrue(
            $schoolLocation1->users()
                ->where(
                    'id',
                    $userFactory->user->getKey())
                ->exists()
        );
        $this->assertEquals("Student", $userFactory->user->roles()->first()->name);
    }

    /** @test */
    public function can_create_multiple_students_with_incrementing_numbers_in_name()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create('S100')->school,
            'SL101'
        )->schoolLocation;

        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, Carbon::today()->format('Y'))
            ->addPeriodFullYear();

        $userFactory1 = FactoryUser::createStudent($schoolLocation);
        $userFactory2 = FactoryUser::createStudent($schoolLocation);

        $createdUsers = $schoolLocation->users->filter(function($user) {
            return $user->roles()->get()->contains(3) && $user->demo == 0;
        });

        $this->assertEquals(2, $createdUsers->count());
        $this->assertEquals('Student 1', $createdUsers->values()[0]->name);
        $this->assertEquals('S1', $createdUsers->values()[0]->abbreviation);
        $this->assertStringContainsString('Student1', $createdUsers->values()[0]->username);
        $this->assertEquals('Student 2', $createdUsers->values()[1]->name);
        $this->assertEquals('S2', $createdUsers->values()[1]->abbreviation);
        $this->assertStringContainsString('Student2', $createdUsers->values()[1]->username);
    }
}