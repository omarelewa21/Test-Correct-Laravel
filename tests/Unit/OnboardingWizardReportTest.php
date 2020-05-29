<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use tcCore\LoginLog;
use tcCore\OnboardingWizardReport;
use tcCore\TestTake;
use tcCore\Text2speech;
use tcCore\Text2speechLog;
use tcCore\User;
use Tests\TestCase;

class OnboardingWizardReportTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    /** @test */
    public function it_should_create_a_new_report_when_user_has_no_record()
    {
        $this->assertCount(
            0,
            OnboardingWizardReport::all()
        );

        OnboardingWizardReport::updateForUser(User::whereUsername(self::USER_TEACHER)->first());

        $this->assertCount(
            1,
            OnboardingWizardReport::all()
        );
    }

    /** @test */
    public function it_should_update_a_report_when_a_user_already_has_a_record()
    {
        OnboardingWizardReport::updateForUser(User::whereUsername(self::USER_TEACHER)->first());
        OnboardingWizardReport::updateForUser(User::whereUsername(self::USER_TEACHER)->first());

        $this->assertCount(
            1,
            OnboardingWizardReport::all()
        );
    }

    /** @test */
    public function it_should_contain_the_email_address_of_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->username,
            OnboardingWizardReport::first()->fresh()->user_email
        );
    }

    /** @test */
    public function it_should_update_a_report_when_has_last_loggedin()
    {
        User::whereUsername(self::USER_TEACHER)->first()->loginLogs()->save(new LoginLog);

        OnboardingWizardReport::updateForUser(User::whereUsername(self::USER_TEACHER)->first());

        $this->assertEquals(
            now(),
            OnboardingWizardReport::first()->fresh()->user_last_login
        );
    }

    /** @test */
    public function it_should_contain_the_name_of_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->name,
            OnboardingWizardReport::first()->fresh()->user_name
        );
    }

    /** @test */
    public function it_should_contain_the_created_at_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->created_at,
            OnboardingWizardReport::first()->fresh()->user_created_at
        );
    }

    /** @test */
    public function it_should_contain_the_first_name_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->name_first,
            OnboardingWizardReport::first()->fresh()->user_name_first
        );
    }
    /** @test */
    public function it_should_contain_the_name_suffix_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->name_suffix,
            OnboardingWizardReport::first()->fresh()->user_name_suffix
        );
    }

    /** @test */
    public function it_should_contain_the_name_of_the_school_location_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->schoolLocation->name,
            OnboardingWizardReport::first()->fresh()->school_location_name
        );
    }

    /** @test */
    public function it_should_contain_the_customer_code_of_the_school_location_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            $teacher->schoolLocation->customer_code,
            OnboardingWizardReport::first()->fresh()->school_location_customer_code
        );
    }

    /** @test */
    public function it_should_contain_the_count_of_the_items_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            9,
            OnboardingWizardReport::first()->fresh()->test_items_created_amount
        );
    }

    /** @test */
    public function it_should_contain_the_amount_of_tests_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            4,
            OnboardingWizardReport::first()->fresh()->tests_created_amount
        );
    }

    /** @test */
    public function it_should_contain_the_first_planned_date_of_a_test_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2020-03-10 00:00:00',
            OnboardingWizardReport::first()->fresh()->first_test_planned_date
        );
    }

    /** @test */
    public function it_should_contain_the_last_planned_date_of_a_test_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2020-03-10 00:00:00',
            OnboardingWizardReport::first()->fresh()->first_test_planned_date
        );
    }

    /** @test */
    public function it_should_contain_the_first_date_of_a_test_taken_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2019-05-24 13:52:42',
            OnboardingWizardReport::first()->fresh()->first_test_taken_date
        );
    }

    /** @test */
    public function it_should_contain_the_last_date_of_a_test_taken_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2019-02-27 14:37:17',
            OnboardingWizardReport::first()->fresh()->last_test_taken_date
        );
    }


    /** @test */
    public function it_should_contain_the_first_date_a_test_was_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            null,
            OnboardingWizardReport::first()->fresh()->test_first_date
        );
    }


    /** @test */
    public function it_should_contain_the_amount_of_the_tests_taken_created_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            2,
            OnboardingWizardReport::first()->fresh()->tests_taken_amount
        );
    }

    /** @test */
    public function it_should_contain_the_test_take_count_discussed_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '0',
            OnboardingWizardReport::first()->fresh()->tests_discussed_amount
        );
    }

    /** @test */
    public function it_should_contain_the_test_take_rated_first_date_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2019-02-27 14:40:38',
            OnboardingWizardReport::first()->fresh()->first_test_rated_date
        );
    }


    /** @test */
    public function it_should_contain_the_test_take_rated_last_date_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2019-02-27 14:43:00',
            OnboardingWizardReport::first()->fresh()->last_test_rated_date
        );
    }

    /** @test */
    public function it_should_contain_onboarding_wizard_mean_time_completing_step_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '7 seconds',
            OnboardingWizardReport::first()->fresh()->average_time_finished_demo_tour_steps_hours
        );
    }

    /** @test */
    public function it_should_contain_the_onboarding_wizard_last_action_in_hours_by_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            '2020-05-25 16:38:05',
            OnboardingWizardReport::first()->fresh()->current_demo_tour_step_since_hours
        );
    }

    /** @test */
    public function it_should_contain_a_comma_delimited_string_of_section_for_the_user()
    {
        $teacher = User::whereUsername(self::USER_TEACHER)->first();
        OnboardingWizardReport::updateForUser($teacher);

        $this->assertEquals(
            ',Nederlands,',
            OnboardingWizardReport::first()->fresh()->user_sections
        );
    }
}
