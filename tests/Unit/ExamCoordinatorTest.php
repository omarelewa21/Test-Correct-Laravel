<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use tcCore\School;
use tcCore\Scopes\ArchivedScope;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\TestCase;

class ExamCoordinatorTest extends TestCase
{
    use DatabaseTransactions;

    private $d1;
    private $d2;
    private $schoolManager;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->d1 = self::getTeacherOne();
        $this->d2 = self::getTeacherTwo();
        $this->schoolManager = self::getSchoolBeheerder();
        $this->actingAs($this->schoolManager);
    }

    /** @test */
    public function can_not_assign_other_than_enum_values_to_exam_coordinator_for_column()
    {
        $this->d1->setAttribute('is_examcoordinator_for', 'something');

        $this->d1->save();
        $this->d1->refresh();

        $this->assertEmpty($this->d1->getAttribute('is_examcoordinator_for'));
    }

    /** @test */
    public function can_assign_enum_values_to_exam_coordinator_for_column()
    {
        $enums = collect($this->d1->getPossibleEnumValues('is_examcoordinator_for'));

        $this->d1->setAttribute('is_examcoordinator_for', $enums->first());

        $this->d1->save();
        $this->d1->refresh();

        $this->assertEquals($enums->first(),$this->d1->getAttribute('is_examcoordinator_for'));
    }

    /** @test */
    public function can_add_school_locations_to_teacher()
    {

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());

        School::find(1)->schoolLocations->each(function ($location) {
            $this->d1->addSchoolLocation($location);
        });

        $this->assertEquals(5, $this->d1->allowedSchoolLocations()->count());
    }

    /** @test */
    public function can_remove_school_locations_from_teacher()
    {
        $this->schoolManager = User::whereUsername(self::USER_SCHOOLBEHEERDER)->first();

        $schoolLocations = School::find(1)->schoolLocations;
        $schoolLocations->each(function ($location) {
            $this->d1->addSchoolLocation($location);
        });
        $this->assertEquals(5, $this->d1->allowedSchoolLocations()->count());


        $locationsToRemove = $schoolLocations->reject(fn($location) => $location->getKey() === $this->schoolManager->schoolLocation->getKey());

        $locationsToRemove->each(function ($location) {
           $this->d1->removeSchoolLocation($location);
        });

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());
    }

    /** @test */
    public function can_make_teacher_with_api_call_as_schoolmanager()
    {
        $this->assertFalse($this->d1->isValidExamCoordinator());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->assertTrue($this->d1->refresh()->isValidExamCoordinator());
    }

    /** @test */
    public function can_add_school_locations_to_teacher_when_is_exam_coordinator_for_school()
    {
        $schoolLocationsInSchool = School::find(1)->schoolLocations()->count();

        $this->assertFalse($this->d1->isValidExamCoordinator());
        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->assertTrue($this->d1->refresh()->isValidExamCoordinator());
        $this->assertEquals($schoolLocationsInSchool, $this->d1->allowedSchoolLocations()->count());
    }

    /** @test */
    public function can_remove_school_locations_from_teacher_when_is_exam_coordinator_for_school_is_changed_to_school_location()
    {
        $schoolLocationsInSchool = School::find(1)->schoolLocations()->count();

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->assertEquals($schoolLocationsInSchool, $this->d1->allowedSchoolLocations()->count());

        $this->setTeacherAsSchoolLocationExamCoordinator()->assertSuccessful();

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());
    }


    /** @test */
    public function can_remove_school_locations_from_teacher_when_is_exam_coordinator_for_school_is_changed_to_none()
    {
        $schoolLocationsInSchool = School::find(1)->schoolLocations()->count();

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->assertEquals($schoolLocationsInSchool, $this->d1->allowedSchoolLocations()->count());

        $this->setTeacherAsNoExamCoordinator()->assertSuccessful();

        $this->assertEquals(1, $this->d1->allowedSchoolLocations()->count());
    }

    /** @test */
    public function can_change_teacher_user_school_location_id_to_school_manager_school_location_id_when_is_exam_coordinator_set_to_none()
    {
        $schoolLocationsInSchool = School::find(1)->schoolLocations->where('id','!=', $this->d1->school_location_id);

        $this->assertNotEquals($this->schoolManager->schoolLocation->getKey(), $this->d1->schoolLocation->getKey());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->setTeacherAsNoExamCoordinator()->assertSuccessful();

        $this->assertEquals($this->schoolManager->schoolLocation->getKey(), $this->d1->refresh()->schoolLocation->getKey());
    }

    /** @test */
    public function can_change_teacher_user_school_location_id_to_school_manager_school_location_id_when_is_exam_coordinator_set_to_school_location()
    {
        $schoolLocationsInSchool = School::find(1)->schoolLocations->where('id','!=', $this->d1->school_location_id);

        $this->assertNotEquals($this->schoolManager->schoolLocation->getKey(), $this->d1->schoolLocation->getKey());

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->setTeacherAsSchoolLocationExamCoordinator()->assertSuccessful();

        $this->assertEquals($this->schoolManager->schoolLocation->getKey(), $this->d1->refresh()->schoolLocation->getKey());
    }

    /** @test */
    public function can_remove_session_hash_when_exam_coordinator_scope_get_changed()
    {
        $this->assertNotEmpty($this->d1->session_hash);

        $this->setTeacherAsSchoolExamCoordinator()->assertSuccessful();

        $this->setTeacherAsSchoolLocationExamCoordinator()->assertSuccessful();

        $this->assertEmpty($this->d1->refresh()->session_hash);
    }

    /**
     * @param null $user
     * @return string
     */
    private function getApiUserUrl($user = null): string
    {
        return sprintf('api-c/user/%s', $user ? $user->uuid : $this->d1->uuid);
    }

    private function setTeacherAsSchoolExamCoordinator($teacher = null)
    {
        return $this->put(
            $this->getApiUserUrl($teacher),
            static::getSchoolBeheerderAuthRequestData([
                'is_examcoordinator'     => '1',
                'is_examcoordinator_for' => 'SCHOOL'
            ])
        );
    }
    private function setTeacherAsSchoolLocationExamCoordinator($teacher = null)
    {
        return $this->put(
            $this->getApiUserUrl($teacher),
            static::getSchoolBeheerderAuthRequestData([
                'is_examcoordinator'     => '1',
                'is_examcoordinator_for' => 'SCHOOL_LOCATION'
            ])
        );
    }
    private function setTeacherAsNoExamCoordinator($teacher = null)
    {
        return $this->put(
            $this->getApiUserUrl($teacher),
            static::getSchoolBeheerderAuthRequestData([
                'is_examcoordinator'     => '0',
                'is_examcoordinator_for' => 'NONE'
            ])
        );
    }

    /** @test */
    public function can_get_all_taken_tests_from_school_location_when_exam_coordinator_from_scope_filtered()
    {
        $ratedTestTakesForD2 = TestTake::filtered(['test_take_status_id' => (string)TestTakeStatus::STATUS_RATED])->count();

        $this->assertFalse($this->d2->isValidExamCoordinator());
        $this->setTeacherAsSchoolExamCoordinator($this->d2)->assertSuccessful();

        $user = $this->d2->refresh();
        $this->actingAs($user);

        $coordinatorCount = TestTake::filtered(['test_take_status_id' => (string)TestTakeStatus::STATUS_RATED])->count();
        $totalSchoolLocationCount = TestTake::where('test_take_status_id', (string)TestTakeStatus::STATUS_RATED)
            ->belongsToSchoolLocation($this->d2)->count();

        $this->assertGreaterThan($ratedTestTakesForD2, $coordinatorCount);
        $this->assertEquals($totalSchoolLocationCount, $coordinatorCount);
    }

    /** @test */
    public function can_open_taken_test_as_exam_coordinator()
    {
        $this->actingAs($this->d1);
        $testTake = TestTake::where('test_take_status_id', (string)TestTakeStatus::STATUS_RATED)
            ->belongsToSchoolLocation($this->d1)
            ->where('user_id', '<>', $this->d1->getKey())
            ->first();

        FactoryScenarioTestTakeRated::create();
        dd($this->get(self::authTeacherOneGetRequest('api-c/test_take/'.$testTake->uuid)));
    }
}