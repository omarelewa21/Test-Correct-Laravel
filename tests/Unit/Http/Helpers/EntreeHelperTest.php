<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\User;
use Tests\TestCase;

class EntreeHelperTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_should_return_false_if_registering_through_entree_and_no_correct_entree_reason()
    {
        session(['entreeReason' => 'zomaar']);
        $this->assertFalse((new EntreeHelper([],null))->handleIfRegistering());
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_and_not_a_teacher()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['eduPersonAffiliation'] = ['student'];
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            'https://www.test-correct.nl/student-aanmelden-error',
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_and_no_eckid()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['eckId'] = [];
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_and_no_valid_brin()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['asdfas'];
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_if_registering_through_entree_and_existing_eckid_with_brin6_and_no_t_user() //4a
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);

        $user->eckid = $data['eckId'][0];
        $user->save();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            '/users/temporary_login/',
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_if_registering_through_entree_and_existing_eckid_brin6_no_t_user_and_same_school() //4b
    {
        // school location should have been added and go to login with message
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);

        $otherSchoolLocation = SchoolLocation::where('school_id',$location->school_id)->where('id','<>',$location->getKey())->first();

        $user->eckid = $data['eckId'][0];
        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->save();
        $user->removeSchoolLocation($location);
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            '/users/temporary_login/',
            $helper->handleIfRegistering()
        );
        $user->refresh();
        $this->assertTrue($user->isAllowedToSwitchToSchoolLocation($location));

    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_and_get_in_contact_if_registering_through_entree_and_existing_eckid_brin6_no_t_user_and_other_school() //4c
    {
        // school location should have been added and go to login with message
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $otherSchoolLocation = SchoolLocation::where('school_id','<>',$location->school_id)->first();

        $user->eckid = $data['eckId'][0];
        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->save();
        $user->removeSchoolLocation($location);
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            'entree_error_message=',
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_entree_registration_with_user_registering_through_entree_and_existing_eckid_brin6_with_t_user() //5
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $helper->handleIfRegistering()
        );

        $this->assertObjectHasAttribute(
            'user',
            session('entreeData')
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_with_allready_registered_if_registering_through_entree_and_existing_eckid_brin4_no_t_user_within_school() //6a
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            ('/users/temporary_login/'),
            $helper->handleIfRegistering()
        );

    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_brin4_no_t_user_other_school() //6b
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);

        $otherSchoolLocation = SchoolLocation::where('school_id','<>',$location->school_id)->first();

        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->removeSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('auth.login'),
            $url
        );
        $this->assertStringContainsString(
            'entree_error_message',
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_with_t_user_and_brin4() //7a
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $helper->handleIfRegistering()
        );
        $this->assertObjectHasAttribute(
            'user',
            session('entreeData')
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_brin4_with_t_user_other_school() //7b
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);

        $otherSchoolLocation = SchoolLocation::where('school_id','<>',$location->school_id)->first();

        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->removeSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('auth.login'),
            $url
        );
        $this->assertStringContainsString(
            'entree_error_message',
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_no_student_error_if_registering_through_entree_and_existing_eckid_but_as_a_student() //8
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getStudentInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            'https://www.test-correct.nl/student-aanmelden-error',
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_with_error_if_registering_through_entree_and_nonexisting_eckid_but_existing_email_address() //10
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $data['eckId'] = [Str::random(10)];
        $data['mail'] = [$user->username];
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $url
        );
        $this->assertStringContainsString(
            rawurlencode(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')),
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_with_error_if_registering_through_entree_and_existing_eckid_and_existing_email_address_for_other_account() //11
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user2 = $this->getTeacherInSchoolLocation($location,true);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $data['mail'] = [$user2->username];
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $url
        );
        $this->assertStringContainsString(
            rawurlencode(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')),
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_with_error_if_registering_through_entree_and_existing_eckid_and_no_email_from_entree() //12
    {
        $location = $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();
        $user->refresh();
        $data['mail'] = [''];
        $helper = new EntreeHelper($data,null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $url
        );
        $this->assertStringContainsString(
            rawurlencode(__('onboarding-welcome.Je entree account kan niet gebruikt worden om een account aan te maken in Test-Correct. Neem contact op met support.')),
            $url
        );
    }


    /**
     * @test
     */
    public function it_should_redirect_to_onboarding_entree_if_registering_through_entree_and_has_valid_brin6()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_onboarding_entree_if_registering_through_entree_and_has_valid_brin4()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $schoolLocation = SchoolLocation::find(84);
        $school = $schoolLocation->school;
        $data['nlEduPersonHomeOrganizationBranchId'] = [$school->external_main_code];
//        $userIds = collect(DB::select(DB::raw('Select user_id from school_location_user where school_location_id = 84')))->map(function($a){ return $a->user_id;});
//        $user = User::whereIn('id',$userIds)->orderBy('created_at','desc')->first();
//        $user->eckid = $data['eckId'][0];
//        $user->save();
        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_onboarding_entree_if_registering_through_entree_and_has_valid_eckid_user()
    {
        $this->setSchoolLocation84ReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $schoolLocation = SchoolLocation::find(84);
        $school = $schoolLocation->school;
        $data['nlEduPersonHomeOrganizationBranchId'] = [$school->external_main_code];
        $userIds = collect(DB::select(DB::raw('select user_id from user_roles where role_id=1')))->map(function($a){return $a->user_id;});
        $user = User::whereIn('id',$userIds)->orderBy('created_at','desc')->first();
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();

        $helper = new EntreeHelper($data,null);
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $helper->handleIfRegistering()
        );
        $entreeData = session('entreeData');
        $this->assertTrue(
            $entreeData->user,
            $user
        );
    }

    protected function getTeacherInSchoolLocation(SchoolLocation $sl, $secondTeacher = false)
    {
        $userIds = collect(DB::select(DB::raw('Select user_id from school_location_user where school_location_id = '.$sl->getKey())))->map(function ($a) {
            return $a->user_id;
        });
        $userIds = collect(DB::select(DB::raw('select user_id from user_roles where role_id=1 AND user_id IN ('.implode(',',$userIds->toArray()).')')))->map(function($a){
            return $a->user_id;
        });
        if($secondTeacher){
            return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->get()[1];
        }
        return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->first();
    }

    protected function getStudentInSchoolLocation(SchoolLocation $sl)
    {
        $userIds = collect(DB::select(DB::raw('Select user_id from school_location_user where school_location_id = '.$sl->getKey())))->map(function ($a) {
            return $a->user_id;
        });
        $userIds = collect(DB::select(DB::raw('select user_id from user_roles where role_id=3 AND user_id IN ('.implode(',',$userIds->toArray()).')')))->map(function($a){
            return $a->user_id;
        });
        return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->first();
    }

    protected function setSchoolLocation84ReadyForTestingRegistration()
    {
        $location = SchoolLocation::findOrFail(84);
        $location->external_main_code = 'K999';
        $location->external_sub_code = '00';
        $location->save();
        return $location;
    }

    protected function getDefaultAttributesForRegistering()
    {
        return [
            'nlEduPersonHomeOrganizationBranchId' => ['K99900'], // brincode
            'mail' => [Str::random(6).'@sobit.nl'],
            'givenName' => [Str::random(6)],
            'eduPersonAffiliation' => ['employee'],
            'eckId' => ['xxxxxrr'],
        ];
    }

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
            route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_not_found']),
            (new EntreeHelper(['nlEduPersonHomeOrganizationBranchId' => ['99DE01']], 'abcd'))->redirectIfBrinUnknown()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_when_no_brin_zes_is_provided()
    {
        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_not_found']),
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
            route('auth.login',
                ['tab' => 'entree', 'entree_error_message' => 'auth.school_info_not_synced_with_test_correct']),
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
            route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.user_not_in_same_school']),
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
            route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.roles_do_not_match_up']),
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

        $helper->laravelUser->is($existingStudent);

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
                'tab'                  => 'entree',
                'entree_error_message' => 'auth.student_account_not_found_in_this_location',
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
        $eckIdRecords = DB::table('eckid_user')->where('user_id', $oldTeacher->id)->get();

        $this->assertCount(1, $eckIdRecords);
        $this->assertEquals(
            $oldTeacher->id,
            $eckIdRecords->first()->user_id
        );
        $this->assertCount(2, ($oldTeacher->refresh())->teacher);
        $this->assertTrue($teacher->refresh()->trashed());
        $this->assertCount(0, ($teacher->refresh())->teacher);
// the laravelUser (the one that we try to login) should be the old teacher;
        $helper->laravelUser->is($oldTeacher);
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

        $importedTeacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $importedTeacher->eckId = 'eckid_L2';
        $importedTeacher->save();

        $oldTeacher = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $oldTeacher->username = 'martin@sobit.nl';
        $oldTeacher->save();

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
        // check that the user we try to login will be the old teacher account;
        $helper->laravelUser->is($oldTeacher);
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

        $this->assertEquals(
            route('auth.login', [
                'tab' => 'entree', 'entree_error_message' => 'auth.email_already_in_use_in_different_school_location'
            ]),
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

    /** @test */
    public function it_should_rollback_and_redirect_scenario2_when_exception_is_thrown_during_transaction_in_handleMatchingTeachersInKoepel(
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

        $helper->shouldThrowAnErrorDuringTransaction = true;

        $this->assertEquals(
            route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.error_while_syncing_please_contact_helpdesk']),
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function it_should_rollback_and_redirect_when_exception_in_scenario2_within_the_same_school_location()
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

        $helper->shouldThrowAnErrorDuringTransaction = true;

        $this->assertEquals(
            route('auth.login',
                ['tab' => 'login', 'entree_error_message' => 'auth.error_while_syncing_please_contact_helpdesk']
            ),
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function if_no_mail_is_found_it_should_block_if_school_lvs_active_no_mail_not_allowed()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        // dit is de geimporteerde docent
        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_T2';
        $teacher->save();

        $this->assertCount(1, $teacher->teacher);


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'eckId'                               => ['eckid_T2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $this->assertEquals(
            route('auth.login',
                ['tab'                  => 'login',
                 'entree_error_message' => 'auth.no_mail_attribute_found_in_saml_request_school_location_does_not_support_login_without_email'
                ]
            ),

            $helper->blockIfSchoolLvsActiveNoMailNotAllowedWhenMailAttributeIsNotPresent()
        );


    }

    /** @test */
    public function it_should_not_block_if_mail_is_not_present_and_school_lvs_active_no_mail_is_allowed()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->lvs_active_no_mail_allowed = true;
        $location->save();

        // dit is de geimporteerde docent
        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = 'eckid_T2';
        $teacher->save();

        $this->assertCount(1, $teacher->teacher);

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'eckId'                               => ['eckid_T2'],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $helper->redirectIfBrinUnknown();

        $this->assertNotEquals(
            route('auth.login',
                ['tab'                  => 'login',
                 'entree_error_message' => 'auth.no_mail_attribute_found_in_saml_request_school_location_does_not_support_login_without_email'
                ]
            ),

            $helper->blockIfSchoolLvsActiveNoMailNotAllowedWhenMailAttributeIsNotPresent()
        );

        $startCount = SamlMessage::count();

        $url = $helper->redirectIfNoMailPresentScenario();
        $this->assertEquals($startCount+1, SamlMessage::count());

        $message = SamlMessage::latest()->first();

        $this->assertEquals(
            route('auth.login', ['tab' => 'no_mail_present', 'uuid' => $message->uuid]),
            $url
        );
    }

    /** @test */
    public function it_should_transfer_external_id_in_entree_helper()
    {
        $user = User::where('username', static::USER_SCHOOLBEHEERDER_LOCATION1)->first();
        $this->actingAs($user);
        $location = SchoolLocation::where('external_main_code', '8888')->where('external_sub_code', '00')->first();
        $oldUser = User::where('username',static::USER_TEACHER)->firstOrFail();
        $user = $this->createTeacher('meOkayOrso', $location);
        $user->eckId = 'eckid_L2';
        $user->external_id = 'test_gmw1';
        $user->save();
        $userId = $user->id;
        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['888800'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L2'],
            ],
            'abcd'
        );
        $helper->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser($oldUser, $user);
        $checkUser = User::withTrashed()->find($userId);
        $this->assertNotNull($checkUser);
        $this->assertNotNull($checkUser->deleted_at);
        $this->assertNull($checkUser->external_id);
        $checkOldUser = User::where('username',static::USER_TEACHER)->firstOrFail();
        $this->assertEquals($checkOldUser->external_id,'test_gmw1');
        $externalIdSchoolLocationUser = DB::table('school_location_user')->where('school_location_id',$location->id)->where('user_id',$checkOldUser->id)->first();
        $this->assertEquals($externalIdSchoolLocationUser->external_id,'test_gmw1');
        $externalIdUser = DB::table('users')->where('id',$checkOldUser->id)->first();
        $this->assertEquals($externalIdUser->external_id,'test_gmw1');
    }
}
