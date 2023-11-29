<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Uuid;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\SamlMessage;
use tcCore\SchoolLocation;
use tcCore\TemporaryLogin;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class EntreeHelperTest extends TestCase
{


    protected $newEckId;


    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('user');
        $this->newEckId = Str::random(6);
    }


    /**
     * @test
     */
    public function it_should_return_false_if_registering_through_entree_and_no_correct_entree_reason()
    {
        session(['entreeReason' => 'zomaar']);
        $this->assertFalse((new EntreeHelper([], null))->handleIfRegistering());
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_and_not_a_teacher() // 1
    {
        $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['eduPersonAffiliation'] = ['student'];
        $helper = new EntreeHelper($data, null);
        $this->assertStringContainsString(
            'https://www.test-correct.nl/welcome-student',
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_when_eckid_attribute_is_array_with_empty_array_value() // 2
    {
        $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['eckId'][0] = [];
        $helper = new EntreeHelper($data, null);
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_when_eckid_attribute_is_empty_array() // 2
    {
        $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['eckId'] = [];
        $helper = new EntreeHelper($data, null);
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_when_no_eckid_is_provided() // 2
    {
        $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        unset($data['eckId']);
        $helper = new EntreeHelper($data, null);
        $this->assertStringContainsString(
            route('onboarding.welcome'),
            $helper->handleIfRegistering()
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_if_registering_through_entree_and_no_valid_brin() // 3
    {
        $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['asdfas'];
        $helper = new EntreeHelper($data, null);
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
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);

        $user->eckid = $data['eckId'][0];
        $user->username = $data['mail'][0];
        $user->save();
        $helper = new EntreeHelper($data, null);
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
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $otherSchoolLocation = FactorySchoolLocation::create($location->school)->schoolLocation;
        $user->eckid = $data['eckId'][0];
        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->save();
        $user->removeSchoolLocation($location);
        $user->refresh();
        $helper = new EntreeHelper($data, null);
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
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $otherSchoolLocation = FactorySchoolLocation::create($location->school)->schoolLocation;

        $user->eckid = $data['eckId'][0];
        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->save();
        $user->removeSchoolLocation($location);
        $user->refresh();
        $helper = new EntreeHelper($data, null);

        $temporaryLoginUuid = collect(explode('/', $helper->handleIfRegistering()))->last();

        $options = json_decode(TemporaryLogin::whereUuid($temporaryLoginUuid)->first()->options, true);

        $this->assertEquals(
            'Je bestaande Test-Correct account is geupdate met de schoollocaties die we vanuit Entree hebben meegekregen. We hebben je in de schoollocatie SimpleSchool gezet. Je kunt vanaf nu ook inloggen met Entree.',
            $options['afterLoginMessage'],
        );

        $this->assertEquals(
            '/users/welcome',
            $options['internal_page'],
        );

        $this->assertTrue($user->isAllowedToSwitchToSchoolLocation($location));
        $this->assertTrue($user->isAllowedToSwitchToSchoolLocation($otherSchoolLocation));
    }

    /**
     * @test
     */
    public function it_should_redirect_to_entree_registration_with_user_registering_through_entree_and_existing_eckid_brin6_with_t_user() //5
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data, null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $url
        );

        $samlMessage = $this->getSamlMessageFromUrl($url);

        $this->assertObjectHasAttribute(
            'user',
            $samlMessage->data
        );
    }

    protected function getSamlMessageFromUrl($url)
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);
        $samlId = $query['samlId'] ?? null;
        $this->assertTrue(!!$samlId);
        $samlMessage = SamlMessage::whereUuid($samlId)->first();
        $this->assertTrue(!!$samlMessage);
        return $samlMessage;
    }

    /**
     * @test
     * @group ignore
     * @TODO Ask Erik
     */
    public function it_should_redirect_to_login_with_already_registered_if_registering_through_entree_and_existing_eckid_brin4_no_t_user_within_school() //6a
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
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
        $helper = new EntreeHelper($data, null);
        $this->assertStringContainsString(
            ('/users/temporary_login/'),
            $helper->handleIfRegistering()
        );

    }

    /**
     * @test
     * @group ignore
     * @TODO Ask Erik
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_brin4_no_t_user_other_school() //6b
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);

        $otherSchoolLocation = FactorySchoolLocation::create($location->school)->schoolLocation;
        $otherSchoolLocation->sso_active = true;
        $otherSchoolLocation->sso_type   = 'Entreefederatie';
        $otherSchoolLocation->save();

        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->removeSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data, null);
        $url = $helper->handleIfRegistering();

        $temporaryLoginUuid = collect(explode('/', $helper->handleIfRegistering()))->last();

        $options = json_decode(TemporaryLogin::whereUuid($temporaryLoginUuid)->first()->options, true);

        $this->assertEquals(
            'Je bestaande Test-Correct account is al gekoppeld aan je Entree account. Je kunt vanaf nu ook inloggen met Entree.',
            $options['afterLoginMessage'],
        );

        $this->assertEquals(
            '/users/welcome',
            $options['internal_page'],
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_with_t_user_and_brin4() //7a
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
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
        $helper = new EntreeHelper($data, null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $url
        );

        $samlMessage = $this->getSamlMessageFromUrl($url);

        $this->assertObjectHasAttribute(
            'user',
            $samlMessage->data
        );
    }

    /**
     * @test
     * @group ignore
     * @TODO Ask Erik
     */
    public function it_should_redirect_to_login_with_get_in_contact_if_registering_through_entree_and_existing_eckid_brin4_with_t_user_other_school() //7b
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        $school = $location->school;
        $school->external_main_code = 'K999';
        $school->save();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $data['nlEduPersonHomeOrganizationBranchId'] = ['K999']; // brincode 4
        $user = $this->getTeacherInSchoolLocation($location);

        $otherSchoolLocation = FactorySchoolLocation::create($location->school)->schoolLocation;

        $user->school_location_id = $otherSchoolLocation->getKey();
        $user->addSchoolLocation($otherSchoolLocation);
        $user->removeSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data, null);
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
     * @group ignore
     * @TODO Ask Erik
     */
    public function it_should_redirect_to_no_student_error_if_registering_through_entree_and_existing_eckid_but_as_a_student() //8
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getStudentInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $user->refresh();
        $helper = new EntreeHelper($data, null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            'https://www.test-correct.nl/student-aanmelden-error',
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_to_entree_registration_if_registering_through_entree_and_nonexisting_eckid_and_unknown_email_address() //9
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $data['eckId'] = [Str::random(10)];
        $helper = new EntreeHelper($data, null);
        $url = $helper->handleIfRegistering();
        $this->assertStringContainsString(
            route('onboarding.welcome.entree'),
            $url
        );
    }

    /**
     * @test
     */
    public function it_should_redirect_with_error_if_registering_through_entree_and_nonexisting_eckid_but_existing_email_address() //10
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $data['eckId'] = [$this->newEckId];
        $data['mail'] = [$user->username];
        $helper = new EntreeHelper($data, null);
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
     * @group ignore
     * @TODO Ask Erik
     *
     */
    public function it_should_redirect_with_error_if_registering_through_entree_and_existing_eckid_and_existing_email_address_for_other_account() //11
    {
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user2 = $this->getTeacherInSchoolLocation($location, true);
        $user->eckid = $data['eckId'][0];
        $user->save();
        $data['mail'] = [$user2->username];
        $helper = new EntreeHelper($data, null);
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
        $location = $this->setSchoolLocationReadyForTestingRegistration();
        session(['entreeReason' => 'register']);
        $data = $this->getDefaultAttributesForRegistering();
        $user = $this->getTeacherInSchoolLocation($location);
        $user->eckid = $data['eckId'][0];
        $user->username = $user->generateMissingEmailAddress();
        $user->save();
        $user->refresh();
        $data['mail'] = [''];
        $helper = new EntreeHelper($data, null);
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

    protected function getTeacherInSchoolLocation(SchoolLocation $sl, $secondTeacher = false)
    {
        $userIds = $this->getSchoolLocationUserIds($sl);
        $userIds = $this->getUsersOfRoleWithIds($userIds, 1);
        if ($secondTeacher) {
            return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->get()[1];
        }
        return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->first();
    }

    protected function getStudentInSchoolLocation(SchoolLocation $sl)
    {
        $userIds = $this->getSchoolLocationUserIds($sl);
        $userIds = $this->getUsersOfRoleWithIds($userIds, 3);

        return User::whereIn('id', $userIds)->orderBy('created_at', 'desc')->first();
    }

    protected function setSchoolLocationReadyForTestingRegistration()
    {
        $location = SchoolLocation::first();
        $location->external_main_code = 'K999';
        $location->external_sub_code = '00';
        $location->save();
        return $location;
    }

    protected function getDefaultAttributesForRegistering()
    {
        return [
            'nlEduPersonHomeOrganizationBranchId' => ['K99900'], // brincode
            'mail'                                => [Str::random(6) . '@sobit.nl'],
            'givenName'                           => ['first_' . Str::random(6)],
            'sn'                                  => ['last_' . Str::random(6)],
            'nlEduPersonTussenvoegsels'           => ['suffix_' . Str::random(6)],
            'eduPersonAffiliation'                => ['employee'],
            'eckId'                               => ['xxxxxrr'],
        ];
    }

    /**
     * @test
     */
    public function it_should_return_the_location_when_a_valid_brin_zes_is_provided()
    {
        // MagisterTestSchool;
        $this->setSchoolLocationReadyForTestingRegistration();
        $location = SchoolLocation::where('external_main_code', 'K999')->where('external_sub_code', '00')->first();
        $this->assertInstanceOf(SchoolLocation::class, $location);

        $this->assertTrue($location->is(
            (new EntreeHelper(['nlEduPersonHomeOrganizationBranchId' => ['K99900']], 'abcd'))->redirectIfBrinUnknown()
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
     * @group ignore
     * @TOD ASK erik this returns brin4Detected which is not correct due to error in EntreeHelper::setLocationWithSamlAttributes()
     */
    public function it_should_redirect_when_no_brin_zes_is_provided()
    {
        $this->assertEquals(
            route('auth.login', ['tab' => 'login', 'entree_error_message' => 'auth.brin_not_found']),
            (new EntreeHelper([], 'abcd'))->redirectIfBrinUnknown()
        );
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
                ['tab' => 'login', 'entree_error_message' => 'auth.school_info_not_synced_with_test_correct']),
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
                'eckId'                               => [],
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
        $student->eckId = $this->newEckId;
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['888800'],
                'mail'                                => ['info+Magister schoollocatie-0@test-correct.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->save();


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->save();


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->save();


        $this->assertNull(
            User::whereUsername('martin@sobit.nl')->first()
        );


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $this->assertFalse($helper->handleScenario2IfAddressIsKnownInOtherAccount());
    }

    /** @test */
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_student_account_within_the_same_location()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $existingStudent = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $existingStudent->username = 'martin@sobit.nl';
        $existingStudent->save();

        $importedStudent = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $importedStudent->eckId = $this->newEckId;
        $importedStudent->save();
        $importedStudent->refresh();

        $this->assertCount(1, $existingStudent->students);
        $this->assertCount(1, $importedStudent->students);

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $helper->handleScenario2IfAddressIsKnownInOtherAccount();

        $this->assertEquals(
            $importedStudent->eckId,
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
        $location2 = SchoolLocation::where('external_main_code', '<>', '99DE')->first();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = $this->newEckId;
        $student->save();

        $student1 = $this->createStudent('meOkayOrso', $location2, null, 'abcdefg');
        $student1->username = 'martin@sobit.nl';
        $student1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
                'eduPersonAffiliation'                => ['Student'],
            ],
            'abcd'
        );

        $this->assertStringContainsString(
            route('auth.login', [
                'tab'                 => 'fatalError',
                'fatal_error_message' => 'auth.student_account_not_found_in_this_location',
            ]),
            $helper->handleScenario2IfAddressIsKnownInOtherAccount()
        );
    }

    /** @test */
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_teacher_account_within_the_same_school_location()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        // dit is de geimporteerde docent
        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = $this->newEckId;
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
                'eckId'                               => [$this->newEckId],
                'eduPersonAffiliation'                => ['Teacher'],
            ],
            'abcd'
        );

        $helper->handleScenario2IfAddressIsKnownInOtherAccount();

        $this->assertEquals($this->newEckId, ($oldTeacher->refresh())->eckId);
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
    public function it_should_handle_scenario2_when_email_addres_is_found_in_other_teacher_account_within_the_same_koepel_but_different_location()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $schoolId = $location->school->getKey();
        $location2 = SchoolLocation::where('external_main_code', '<>', '99DE')->where('school_id', $schoolId)->first();

        // in dezelfde koepel
        $this->assertTrue(
            $location->school->is($location2->school)
        );

        // maar niet in dezelfde locatie;
        $this->assertFalse(
            $location->is($location2)
        );

        $importedTeacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $importedTeacher->eckId = $this->newEckId;
        $importedTeacher->save();

        $oldTeacher = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $oldTeacher->username = 'martin@sobit.nl';
        $oldTeacher->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
    public function it_should_redirect_scenario2_when_email_addres_is_found_in_other_teacher_account_within_a_different_koepel()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location2 = SchoolLocation::where('external_main_code', '<>', '99DE')->where('school_id', '<>', $location->school_id)->first();


        // Niet in dezelfde koepel
        $this->assertFalse(
            $location->school->is($location2->school)
        );

        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = $this->newEckId;
        $teacher->save();

        $teacher1 = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $teacher1->username = 'martin@sobit.nl';
        $teacher1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $teacher->eckId = $this->newEckId;
        $this->assertEmpty($teacher->external_id);
        $teacher->username = sprintf(User::TEACHER_IMPORT_EMAIL_PATTERN, $teacher->id);
        $teacher->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['teacher@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $this->assertEmpty($student->external_id);
        $student->username = sprintf(User::STUDENT_IMPORT_EMAIL_PATTERN, $student->id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->username = 'test_user@sobit.nl';
        $this->assertEmpty($student->external_id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $student->eckId = $this->newEckId;
        $student->username = 'test_user@sobit.nl';
        $this->assertEmpty($student->external_id);
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
    public function handle_scenario1_for_student_when_external_id_is_already_set_the_new_email_should_not_make_new_external_id()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location->is_rtti_school_location = 1;
        $location->save();

        $student = $this->createStudent('meOkayOrso', $location, null, 'abcdefg');
        $student->eckId = $this->newEckId;
        $student->username = 'test_user@sobit.nl';
        $student->external_id = 'test_external_id';
        $student->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
    public function it_should_rollback_and_redirect_scenario2_when_exception_is_thrown_during_transaction_in_handleMatchingTeachersInKoepel()
    {

        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $location2 = SchoolLocation::where('external_main_code', '<>', '99DE')->where('school_id', $location->school_id)->first();

        // in dezelfde koepel
        $this->assertTrue(
            $location->school->is($location2->school)
        );

        // maar niet in dezelfde locatie;
        $this->assertFalse(
            $location->is($location2)
        );

        $teacher = $this->createTeacher('meOkayOrso', $location, null, 'abcdefg');
        $teacher->eckId = $this->newEckId;
        $teacher->save();

        $teacher1 = $this->createTeacher('meOkayOrso', $location2, null, 'abcdefg');
        $teacher1->username = 'martin@sobit.nl';
        $teacher1->save();

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
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
        $teacher->eckId = $this->newEckId;
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
                'eckId'                               => [$this->newEckId],
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
        $teacher->eckId = $this->newEckId;
        $teacher->save();

        $this->assertCount(1, $teacher->teacher);


        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'eckId'                               => [$this->newEckId],
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
        $teacher->eckId = $this->newEckId;
        $teacher->username = $teacher->generateMissingEmailAddress();
        $teacher->save();

        $this->assertCount(1, $teacher->teacher);

        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'eckId'                               => [$this->newEckId],
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
        $this->assertEquals($startCount + 1, SamlMessage::count());

        $message = SamlMessage::latest()->first();

        $this->assertEquals(
            route('auth.login', ['tab' => 'no_mail_present', 'uuid' => $message->uuid]),
            $url
        );
    }

    /** @test */
    public function it_should_transfer_external_id_in_entree_helper()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();
        $expression = DB::raw('select user_id from user_roles where role_id = 1');
        $userIds = collect(DB::select($expression->getValue(DB::connection()->getQueryGrammar())))->map(function ($a) {
            return $a->user_id;
        });

        $user = User::where('school_location_id', $location->getKey())->whereIn('id', $userIds)->first();

        $this->actingAs($user);
        $oldUser = User::where('username', '<>', $user->getKey())->where('school_location_id', $location->getKey())->whereIn('id', $userIds)->firstOrFail();
        $oldUsername = $oldUser->username;
        $user = $this->createTeacher('meOkayOrso', $location);
        $user->eckId = $this->newEckId;
        $user->external_id = 'test_gmw1';
        $user->save();
        $userId = $user->id;
        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => [$this->newEckId],
            ],
            'abcd'
        );

        $helper->copyEckIdNameNameSuffixNameFirstAndTransferClassesUpdateTestParticipantsAndDeleteUser($oldUser, $user);
        $checkUser = User::withTrashed()->find($userId);
        $checkUser->refresh();
        $this->assertNotNull($checkUser);
        $this->assertNotNull($checkUser->deleted_at);
        $this->assertNull($checkUser->external_id);
        $checkOldUser = User::where('username', $oldUsername)->firstOrFail();
        $checkOldUser->refresh();
        $this->assertEquals($checkOldUser->external_id, 'test_gmw1');
        $externalIdSchoolLocationUser = DB::table('school_location_user')->where('school_location_id', $location->id)->where('user_id', $checkOldUser->id)->first();
        $this->assertEquals($externalIdSchoolLocationUser->external_id, 'test_gmw1');
        $externalIdUser = User::where('id', $checkOldUser->id)->first();
        $externalIdUser->refresh();
        $this->assertEquals($externalIdUser->external_id, 'test_gmw1');
    }

    /**
     * @param SchoolLocation $sl
     * @return \Illuminate\Support\Collection
     */
    private function getSchoolLocationUserIds(SchoolLocation $sl): \Illuminate\Support\Collection
    {
        $expression = DB::raw('Select user_id from school_location_user where school_location_id = ' . $sl->getKey());
        return collect(
            DB::select($expression->getValue(DB::connection()->getQueryGrammar()))
        )->map(function ($a) {
            return $a->user_id;
        });
    }

    /**
     * @param \Illuminate\Support\Collection $userIds
     * @return \Illuminate\Support\Collection
     */
    private function getUsersOfRoleWithIds(\Illuminate\Support\Collection $userIds, int $roleId): \Illuminate\Support\Collection
    {
        $expression = DB::raw(
            'select user_id from user_roles where role_id=' . $roleId . ' AND user_id IN (' . implode(
                ',',
                $userIds->toArray()
            ) . ')'
        );
        return collect(
            DB::select(
                $expression->getValue(DB::connection()->getQueryGrammar())
            )
        )->map(function ($a) {
            return $a->user_id;
        });
    }
}
