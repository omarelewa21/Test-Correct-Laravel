<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class EntreeHelperTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_should_return_the_location_when_a_valid_brin_zes_is_provided()
    {
        // MagisterTestSchool;
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $this->assertInstanceOf(SchoolLocation::class, $location);

        $this->assertTrue($location->is(
            (new EntreeHelper(['nlEduPersonHomeOrganizationBranchId' => ['99DE00']], 'abcd'))->redirectIfBrinUnknown()
        ));
    }

    /**
     * @test
     */
    public function it_should_redirect_when_a_invalid_brin_zes_is_provided()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '01')->first();
        $this->assertNull($location);

        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']),
            (new EntreeHelper(['nlEduPersonHomeOrganizationBranchId' => ['99DE01']], 'abcd'))->redirectIfBrinUnknown()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_when_no_brin_zes_is_provided()
    {
        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']),
            (new EntreeHelper([], 'abcd'))->redirectIfBrinUnknown()
        );
    }

    /** @test */
    public function redirectIfScenario5_should_return_true_if_lvs_active_on_location()
    {
        $helper = new EntreeHelper(['nlEduPersonHomeOrganizationBranchId' => ['99DE00']], 'abcd');
        $location = $helper->redirectIfBrinUnknown();
        $this->assertTrue($location->lvs_active);
        $this->assertTrue($helper->redirectIfscenario5());
    }

    /** @test */
    public function it_should_redirect_when_no_user_is_found_with_provided_eck_id()
    {
        $helper = new EntreeHelper([
            'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
            'eckId'                               => ['eckId_L1'],
            'mail'                                => ['martin@sobit.nl'],
        ], 'abc');

        $this->assertStringContainsString(
            route('auth.login', ['tab' => 'entree', 'message' => '']),
            $helper->redirectIfNoUserWasFoundForEckId()
        );
    }

    /**
     * @test
     */
    public function it_should_throw_an_error_if_eckId_not_in_saml_request()
    {
        $this->expectException(\Exception::class);
        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'eckId'                               => ['eckId_L1'],
            ],
            'abcd'
        );
        $helper->redirectIfBrinUnknown();
        $helper->redirectIfNoUserWasFoundForEckId();
    }

    /** @test */
    public function it_should_thow_an_error_if_mail_not_found_in_saml_request()
    {
        $this->expectException(\Exception::class);
        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
            ],
            'abcd'
        );
        $helper->redirectIfBrinUnknown();
        $helper->redirectIfNoUserWasFoundForEckId();
    }

    /** @test */
    public function laravel_user_should_be_in_the_same_school_as_brin_provided()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->save();


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
            ],
            'abcd'
        );

        $this->assertTrue($helper->redirectIfUserNotInSameSchool());

    }

    /** @test */
    public function if_laravel_user_not_in_same_same_school_location_as_brin_provided_it_should_redirect()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L1';
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['888800'],
                'mail'                                => ['info+Magister schoollocatie-0@test-correct.nl'],
                'eckId'                               => ['eckid_L1'],
            ],
            'abcd'
        );
        $this->assertStringContainsString(
            route('auth.login', ['tab' => 'entree', 'message' => 'oeps']),
            $helper->redirectIfUserNotInSameSchool()
        );
    }

    /**
     * @test
     */
    public function it_should_return_true_if_laravel_user_has_the_same_role_as_in_entree()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->save();


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $this->assertTrue($helper->redirectIfUserNotHasSameRole());
    }

    /**
     * @test
     */
    public function it_should_redirect_if_laravel_user_doesnt_have_the_same_role_as_in_entree()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->save();


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $this->assertStringContainsString(
            route('auth.login', ['tab' => 'entree', 'message' => '']),
            $helper->redirectIfUserNotHasSameRole()
        );
    }

    /** @test */
    public function it_should_return_false_when_email_adress_is_unknown_in_other_account()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->save();


        $this->assertNull(
            User::whereUsername('martin@sobit.nl')->first()
        );

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $this->assertFalse($helper->handleScenario2IfAddressIsKnownInOtherAccount());
    }

    /** @test */
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_student_account_within_the_same_location(
    )
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $existingStudent = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $existingStudent->username = 'martin@sobit.nl';
        $existingStudent->save();

        $importedStudent = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $importedStudent->eckId = 'eckid_L2';
        $importedStudent->save();

        $this->assertCount(1, $existingStudent->students);
        $this->assertCount(1, $importedStudent->students);


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $helper->handleScenario2IfAddressIsKnownInOtherAccount();

        $this->assertEquals(
            'eckid_L2',
            $existingStudent->eckId
        );

        $this->assertTrue($importedStudent->refresh()->trashed());

        $this->assertCount(0, ($importedStudent->refresh())->students);
        $this->assertCount(2, ($existingStudent->refresh())->students);
    }

    /** @test */
    public function it_should_redirect_when_email_addres_is_found_in_other_student_account_within_another_location()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location2 = SchoolLocation::where('external_main_code', '8888')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->save();

        $student1 = $this->createStudent('meOkayOrso', $location2, null, 'abcdefg');
        $student1->username = 'martin@sobit.nl';
        $student1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $this->assertStringContainsString(
            route('auth.login', [
                'tab'     => 'entree',
                'message' => 'Je bent in Test-Correct niet gekoppeld aan de gekozen schoollocatie. Kies de juiste schoollocatie'
            ]),
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_teacher_account_within_the_same_school_location(
    )
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        // dit is de geimporteerde docent
        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_T2';
        $teacher->save();

        $this->assertCount(1, $teacher->teacher);

        // dit is de oude docent;
        $oldTeacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $oldTeacher->username = 'martin@sobit.nl';
        $oldTeacher->save();
        $this->assertCount(1, $oldTeacher->teacher);

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_T2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $helper->handleScenario2IfAddressIsKnownInOtherAccount();

        $this->assertEquals('eckid_T2', ($oldTeacher->refresh())->eckId);
        $eckIdRecords = DB::table('eckid_user')->where('eckid', 'eckid_T2')->get();
        $this->assertCount(1, $eckIdRecords);
        $this->assertEquals(
            $oldTeacher->id,
            $eckIdRecords->first()->user_id
        );
        $this->assertCount(2, ($oldTeacher->refresh())->teacher);
        $this->assertTrue($teacher->refresh()->trashed());
        $this->assertCount(0, ($teacher->refresh())->teacher);
    }

    /** @test */
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_teacher_account_within_the_same_koepel_but_different_location(
    )
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location2 = SchoolLocation::where('external_main_code', '8888')->where('external_sub_code', '00')->first();

        // in dezelfde koepel
        $this->assertTrue(
            $location->school->is($location2->school)
        );

        // maar niet in dezelfde locatie;
        $this->assertFalse(
            $location->is($location2)
        );

        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_L2';
        $teacher->save();

        $teacher1 = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $teacher1->username = 'martin@sobit.nl';
        $teacher1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $this->assertTrue(
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function it_should_redirect_scenario2_when_email_addres_is_found_in_other_teacher_account_within_a_different_koepel(
    )
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location2 = SchoolLocation::where('external_main_code', '8888')->where('external_sub_code', '00')->first();
        $location2->school_id = 2;
        $location2->save();

        // Niet in dezelfde koepel
        $this->assertFalse(
            $location->school->is($location2->school)
        );

        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_L2';
        $teacher->save();

        $teacher1 = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $teacher1->username = 'martin@sobit.nl';
        $teacher1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $this->assertStringContainsString(
            'other%20user%20is%20using%20emailaddress%20not%20in%20same%20koepel%20not%20in%20same%20school',
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function handle_scenario1_for_teacher_should_overwrite_import_email_pattern_and_empty_external_id()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 1;
        $location->save();

        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_T2';
        $this->assertEmpty($teacher->external_id);
        $teacher->username = sprintf(User::TEACHER_IMPORT_EMAIL_PATTERN, $teacher->id);
        $teacher->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['teacher@sobit.nl'],
                'eckId'                               => ['eckid_T2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $redirectUrl = $helper->handleScenario1();

// should redirect to temporary_login
        $this->assertEquals(
            sprintf('%susers/temporary_login/', config('app.url_login')),
            substr($redirectUrl, 0, -36)
        );
        $this->assertEquals(
            'teacher@sobit.nl',
            ($teacher->refresh())->username
        );

        $this->assertEmpty(
            ($teacher->refresh())->external_id
        );
    }

    /** @test */
    public function handle_scenario1_for_student_with_student_import_email_pattern_and_empty_external_id()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 1;
        $location->save();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $this->assertEmpty($student->external_id);
        $student->username = sprintf(User::STUDENT_IMPORT_EMAIL_PATTERN, $student->id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $redirectUrl = $helper->handleScenario1();

// should redirect to temporary_login
        $this->assertEquals(
            sprintf('%susers/temporary_login/', config('app.url_login')),
            substr($redirectUrl, 0, -36)
        );
        $this->assertEquals(
            'martin@sobit.nl',
            ($student->refresh())->username
        );

        $this->assertEquals(
            'martin',
            ($student->refresh())->external_id
        );
    }

    /** @test */
    public function handle_scenario1_for_student_when_student_already_has_email_set_it_also_update_to_new_version()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 1;
        $location->save();


        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->username = 'test_user@sobit.nl';
        $this->assertEmpty($student->external_id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );


        $redirectUrl = $helper->handleScenario1();

// should redirect to temporary_login
        $this->assertEquals(
            sprintf('%susers/temporary_login/', config('app.url_login')),
            substr($redirectUrl, 0, -36)
        );
        $this->assertEquals(
            'martin@sobit.nl',
            ($student->refresh())->username
        );
        $this->assertEquals('martin', $student->external_id);
    }

    /** @test */
    public function handle_scenario1_it_should_not_set_external_id_when_not_a_rtti_school()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 0;
        $location->save();


        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->username = 'test_user@sobit.nl';
        $this->assertEmpty($student->external_id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );


        $redirectUrl = $helper->handleScenario1();

// should redirect to temporary_login
        $this->assertEquals(
            sprintf('%susers/temporary_login/', config('app.url_login')),
            substr($redirectUrl, 0, -36)
        );
        $this->assertEquals(
            'martin@sobit.nl',
            ($student->refresh())->username
        );
        $this->assertNotEquals('martin', $student->external_id);
    }


    /** @test */
    public function handle_scenario1_for_student_when_external_id_is_already_set_the_new_email_should_not_make_new_external_id(
    )
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 1;
        $location->save();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = 'eckid_L2';
        $student->username = 'test_user@sobit.nl';
        $student->external_id = 'test_external_id';
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );


        $redirectUrl = $helper->handleScenario1();

// should redirect to temporary_login
        $this->assertEquals(
            sprintf('%susers/temporary_login/', config('app.url_login')),
            substr($redirectUrl, 0, -36)
        );
        $this->assertEquals(
            'martin@sobit.nl',
            ($student->refresh())->username
        );
        $this->assertEquals(
            'test_external_id',
            $student->external_id
        );
    }


}
