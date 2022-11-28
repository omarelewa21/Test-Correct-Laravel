<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\MigrateUserService;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class MigrateUserServiceTest extends TestCase
{
    use DatabaseTransactions;


    /**
     * 1. Beide accounts zelfde schoollocatie
     * 2. Beide accounts zelfde voornaam
     * 3. Beide accounts zelfde achternaam
     * 4. Account nieuw moet beginnen met t_ of s_
     * 5. Beide accounts moeten dezelfde rol hebben
     */

    /** @test */
    public function it_should_merge_the_accounts_for_students()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');

        $oldUser->name_first = $user->name_first = 'Martin';
        $oldUser->name =  $user->name = 'Folkerts';
        $user->save();
        $oldUser->save();

        $user->username = $user->generateMissingEmailAddress();

        $user->save();
        $oldUser->save();

        $this->assertTrue(
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );

//        $this->mock(EntreeHelper::class, function($mock) {
//            $mock->shouldReceive('copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser')->once();
//        });



    }

    /**
     * @test
     */
    public function it_should_error_when_both_ids_are_identical()
    {
        $this->assertEquals(
            'An error occured: oldId can not be Id (1, 1)',
            (new MigrateUserService(1, 1))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_both_users_are_not_in_same_school_location()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createStudent('abc', SchoolLocation::find(2), null, 'abcdef');

        $this->assertEquals(
            'An error occured: SchoolLocation are not the same',
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_both_users_dont_have_the_same_role()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createTeacher('abc', SchoolLocation::find(1), null, 'abcdef');

        $user->save();
        $user->name = 'same';
        $user->name_first = 'same_first';
        $oldUser->name = 'same';
        $oldUser->name_first = 'same_first';
        $user->username = $user->generateMissingEmailAddress();

        $oldUser->save();
        $user->save();

        $this->assertEquals(
            'An error occured: Roles are not the same.',
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );
    }


    /**
     * @test
     */
    public function it_should_error_when_oldId_is_not_a_propper_user_id()
    {
        $this->assertEquals(
            'An error occured: oldId 10 is not a propper user_id',
            (new MigrateUserService(10, 11))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_id_is_not_a_propper_user_id()
    {
        $this->assertEquals(
            'An error occured: id 11111 is not a propper user_id',
            (new MigrateUserService(1483, 11111))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_name_attributes_dont_match()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');

        $oldUser->name = 'OldUser';
        $oldUser->save();
        $user->name = 'NewUser';
        $user->save();

        $this->assertNotEquals(
            $oldUser->name,
            $user->name
        );

        $this->assertEquals(
           'An error occured: names are not the same [OldUser] , [NewUser].',
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_first_name_attributes_dont_match()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');

        $oldUser->name = 'Same';
        $oldUser->name_first = 'same';
        $oldUser->save();
        $user->name = 'Same';
        $user->name_first= 'different';
        $user->save();

        $this->assertNotEquals(
            $oldUser->name_first,
            $user->name_first
        );

        $this->assertEquals(
            'An error occured: first names are not the same [same] , [different].',
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );
    }

    /** @test */
    public function it_should_error_when_username_user_is_not_import_emailaddress()
    {
        $oldUser = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');
        $user = $this->createStudent('abc', SchoolLocation::find(1), null, 'abcdef');


        $this->assertFalse(
            $user->hasImportMailAddress()
        );

        $this->assertEquals(
            'An error occured: user should have importMailAddress.',
            (new MigrateUserService($oldUser->getKey(), $user->getKey()))->handle()
        );
    }



}
