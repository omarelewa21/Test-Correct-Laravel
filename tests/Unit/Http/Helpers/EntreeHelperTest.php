<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Foundation\Testing\DatabaseTransactions;
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
        dd(User::findByEckId('eckid_L1')->first());
        $helper = new EntreeHelper(
            [
                'nlEduPersonHomeOrganizationBranchId' => ['99DE00'],
                'mail'                                => ['martin@sobit.nl'],
                'eckId'                               => ['eckid_L1'],
            ],
            'abcd'
        );

        $this->assertTrue($helper->redirectIfUserNotInSameSchool());

    }

    /** @test */
    public function if_laravel_user_not_in_same_same_school_location_as_brin_provided_it_should_redirect()
    {
        $location = SchoolLocation::where('external_main_code', '99DE')->where('external_sub_code', '00')->first();

        $student = $this->createStudent('meOkayOrso', $location, null , 'abcdefg');
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

        $this->assertTrue($helper->redirectIfUserNotInSameSchool());
    }
}
