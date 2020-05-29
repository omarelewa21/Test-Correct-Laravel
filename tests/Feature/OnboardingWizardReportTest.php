<?php

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use tcCore\Exceptions\Handler;
use tcCore\Teacher;
use tcCore\User;
use Tests\TestCase;

class OnboardingWizardReportTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_create_a_export_file_in_the_storage_dir()
    {
        $file = storage_path('onboarding_wizard_report.xls');

        if (file_exists($file)) {
            unlink($file);
        }

        $this->assertFalse(
            file_exists(storage_path('onboarding_wizard_report.xls'))
        );

        \tcCore\OnboardingWizardReport::updateForAllTeachers();

        $this->post(route('onboarding_wizard_report.store'), static::getAccountManagerAuthRequestData());
        $this->assertTrue(
            file_exists(storage_path('onboarding_wizard_report.xls'))
        );
    }

    /** @test */
    public function it_should_update_the_onboarding_reports_table()
    {
        $this->assertCount(
            0,
            \tcCore\OnboardingWizardReport::all()
        );

       \tcCore\OnboardingWizardReport::updateForAllTeachers();

        $this->assertCount(
            4,
            \tcCore\OnboardingWizardReport::all()
        );

    }

}
