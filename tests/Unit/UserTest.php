<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use tcCore\ArchivedModel;
use tcCore\EckidUser;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioSchoolRandomComplex;
use tcCore\FactoryScenarios\FactoryScenarioSchoolRtti;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\School;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\TestTake;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolRtti::class;

    protected function setUp(): void
    {
        parent::setUp();
    }

//    /** @test */
    public function when_deleting_a_teacher_that_is_a_member_of_two_school_location_the_teacher_gets_removed_the_memberships_table_not_deleted()
    {
        $adminA = User::whereUsername('admin-a@test-correct.nl')->first();
        $this->actingAs($adminA);

        $teacherA = User::whereUsername('teacher-a@test-correct.nl')->first();

        $this->assertTrue(
            $teacherA->isAllowedToSwitchToSchoolLocation($adminA->schoolLocation)
        );

        $teacherA->delete();

        $this->assertFalse(
            $teacherA->isAllowedToSwitchToSchoolLocation($adminA->schoolLocation)
        );

        $this->assertNotNull($teacherA->refresh());

        $teacherA->delete();
        $this->assertNull(User::whereUsername('teacher-a@test-correct.nl')->first());
    }

    /** @test */
    public function after_create_a_teacher_has_a_school_location()
    {
        $data = [
            'school_location_id' => '2',
            'name_first'         => 'a',
            'name_suffix'        => '',
            'name'               => 'bc',
            'abbreviation'       => 'abcc',
            'username'           => 'abc@test-correct.nl',
            'password'           => '12345678',
            'external_id'        => 'abc',
            'note'               => '',
            'user_roles'         => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $this->assertNotNull($rData['school_location']);
    }

    /** @test */
    public function after_update_a_teacher_has_a_school_location_different_external_id_in_school_location_user()
    {
        $data = [
//            'school_location_id' => '2',
            'name_first'   => 'a',
            'name_suffix'  => '',
            'name'         => 'bc',
            'abbreviation' => 'abcc',
            'username'     => 'abc@test-correct.nl',
            'password'     => '12345678',
            'external_id'  => 'abc',
            'note'         => '',
            'user_roles'   => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $user = User::find($rData['id']);
        $this->assertNotNull($rData['school_location']);
        $schoolLocationId = $rData['school_location']['id'];
        $schoolLocations = $user->allowedSchoolLocations()->get();
        foreach ($schoolLocations as $schoolLocation) {
            $this->assertEquals('abc', $schoolLocation->pivot->external_id);
            $this->assertEquals($schoolLocationId, $schoolLocation->pivot->school_location_id);
            //dump($schoolLocation->pivot->external_id);
        }
        $data['id'] = $rData['id'];
        $data['uuid'] = $rData['uuid'];
        $data['external_id'] = 'cde';
        $response = $this->put(
            'api-c/user/' . $rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $schoolLocations = $user->allowedSchoolLocations()->get();
        foreach ($schoolLocations as $schoolLocation) {
            $this->assertEquals('cde', $schoolLocation->pivot->external_id);
            //dump($schoolLocation->pivot->external_id);
        }
    }

    /** @test */
    public function create_and_update_teacher_fails_when_external_already_exists_in_school_location()
    {
        $data = [
            'school_location_id' => '2',
            'name_first'         => 'a',
            'name_suffix'        => '',
            'name'               => 'bc',
            'abbreviation'       => 'abcc',
            'username'           => 'abc@test-correct.nl',
            'password'           => '12345678',
            'external_id'        => 'abc',
            'note'               => '',
            'user_roles'         => [1],
        ];

        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        //dump($response->getContent());
        $response->assertStatus(200);
        $rData = $response->decodeResponseJson();
        $data['username'] = 'cde@test-correct.nl';
        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(422);
        $data['external_id'] = 'cde';
        $response = $this->post(
            'api-c/user',
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
        $data['username'] = 'abc@test-correct.nl';
        $response = $this->put(
            'api-c/user/' . $rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(422);
        $data['external_id'] = 'efg';
        $response = $this->put(
            'api-c/user/' . $rData['uuid'],
            static::getRttiSchoolbeheerderAuthRequestData($data)
        );
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_store_a_user_with_a_eckid()
    {
        $eckIdUserCount = EckidUser::count();

        $user = factory(User::class)->create(['user_roles' => 1]);
        $user->eckId = 'ABCDEF';
        $user->save();

        $this->assertNotEquals($eckIdUserCount, EckidUser::count());
    }

    /** @test */
    public function it_can_retrieve_a_user_by_eckId()
    {
        $eckId = 'ABCDEF';
        $this->assertEquals(0, EckidUser::count());

        $user = factory(User::class)->create(['user_roles' => 1]);
        $user->eckId = $eckId;
        $user->save();

        $userFromDB = User::findByEckId('ABCDEF')->first();
        $this->assertTrue($user->is($userFromDB));
    }

    /** @test */
    public function when_a_user_is_a_teacher_and_not_all_classes_with_an_import_record_are_checked_it_should_return_false()
    {
        $this->markTestIncomplete(); /* Needs an LVS Scenario */
        $teacherOne = User::where('username', 'd1@test-correct.nl')->first();
        $this->assertFalse($teacherOne->hasIncompleteImport());
    }

    /** @test */
    public function when_classes_get_transfered_from_teacher_to_teacher_the_one_the_function_is_called_on_should_have_the_classes()
    {
        $location = ScenarioLoader::get('school_locations')->first();

        $teacherOne = $this->createTeacher('password', $location, null);
        $teacherTwo = $this->createTeacher('password', $location, null);

        $this->assertCount(1, $teacherOne->teacher);
        $this->assertCount(1, $teacherTwo->teacher);

        $teacherOne->transferClassesFromUser($teacherTwo);

        $this->assertCount(2, ($teacherOne->refresh())->teacher);
        $this->assertCount(0, ($teacherTwo->refresh())->teacher);
    }

    /** @test */
    public function when_classes_get_transfered_and_both_teacher_are_linked_to_the_same_class_nothing_happens_to_the_receiving_teacher_but_the_from_teacher_gets_removed_from_the_class()
    {
        $location = ScenarioLoader::get('school_locations')->first();

        $teacherOne = $this->createTeacher('password', $location, null);
        $schoolClass = $teacherOne->teacher->first()->schoolClass;

        $teacherTwo = $this->createTeacher('password', $location, $schoolClass);

        $this->assertCount(1, $teacherOne->teacher);
        $this->assertCount(1, $teacherTwo->teacher);

        $teacherOne->transferClassesFromUser($teacherTwo);

        $this->assertCount(1, ($teacherOne->refresh())->teacher);
        $this->assertCount(0, ($teacherTwo->refresh())->teacher);
    }

    /**
     * @test
     */
    public function when_an_old_teacher_is_linked_to_a_trashed_school_class_and_the_imported_user_has_access_to_this_class_it_gets_restored()
    {
        $location = ScenarioLoader::get('school_locations')->first();

        $teacherOne = $this->createTeacher('password', $location, null);
        $schoolClass = $teacherOne->teacher->first()->schoolClass;
        $teacherOne->teacher->first()->delete();

        $teacherTwo = $this->createTeacher('password', $location, $schoolClass);

        $this->assertCount(1, $teacherOne->teacher()->withTrashed()->get());

        $this->assertCount(0, ($teacherOne->refresh())->teacher);
        $this->assertCount(1, ($teacherTwo->refresh())->teacher);

        $teacherOne->transferClassesFromUser($teacherTwo);

        $this->assertCount(1, ($teacherOne->refresh())->teacher);
        $this->assertCount(0, $teacherOne->teacher()->onlyTrashed()->get());
        $this->assertCount(0, ($teacherTwo->refresh())->teacher);
    }


    /**
     * @test
     */
    public function when_an_imported_teacher_is_linked_to_two_classes_both_get_transferred_to_the_old_teacher()
    {
        $location = ScenarioLoader::get('school_locations')->first();

        $teacherOne = $this->createTeacher('password', $location, null);
        $teacherTwo = $this->createTeacher('password', $location);

        $schoolClassThree = SchoolClass::create([
            'school_location_id'              => $location->getKey(),
            'education_level_id'              => 12,
            'school_year_id'                  => $location->schoolLocationSchoolYears->first()->school_year_id,
            'name'                            => 'other name',
            'education_level_year'            => 2,
            'is_main_school_class'            => 1,
            'do_not_overwrite_from_interface' => 0,
        ]);
        Teacher::create([
            'user_id'    => $teacherTwo->getKey(),
            'class_id'   => $schoolClassThree->getKey(),
            'subject_id' => 30,
        ]);

        $this->assertCount(1, $teacherOne->teacher()->withTrashed()->get());
        $this->assertCount(2, ($teacherTwo->refresh())->teacher);

        $teacherOne->transferClassesFromUser($teacherTwo);

        $this->assertCount(3, ($teacherOne->refresh())->teacher);
        $this->assertCount(0, ($teacherTwo->refresh())->teacher);
    }

    /** @test */
    public function deleting_user_with_id_in_schools_table_is_not_allowed()
    {
        $this->expectExceptionMessage(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een scholengemeenschap'));
        $school = School::whereNotNull('user_id')->first();
        $user = User::find($school->user_id);
        $user->delete();
        $user = User::find($school->user_id);
        $this->assertNotNull($user);
    }

    /** @test */
    public function deleting_user_with_id_in_school_locations_table_is_not_allowed()
    {
        $this->expectExceptionMessage(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een schoollocatie'));

        $schoolLocation = SchoolLocation::whereNotNull('user_id')->first();
        $user = User::find($schoolLocation->user_id);
        $user->delete();
        $user = User::find($schoolLocation->user_id);
        $this->assertNotNull($user);
    }

    /** @test */
    public function deleting_user_with_id_in_umbrella_organisations_table_is_not_allowed()
    {
        $this->expectExceptionMessage(__('Kan gebruiker niet verwijderen omdat deze gekoppeld is aan een koepel'));

        $umbrellaOrganisation = UmbrellaOrganization::whereNotNull('user_id')->first();
        $user = User::find($umbrellaOrganisation->user_id);
        $user->delete();
        $user = User::find($umbrellaOrganisation->user_id);
        $this->assertNotNull($user);
    }
}
