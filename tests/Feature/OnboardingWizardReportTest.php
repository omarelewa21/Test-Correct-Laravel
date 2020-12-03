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

        $faker = Faker\Factory::create();;
        Teacher::limit(4)->groupBy('user_id')->get()->each(function(Teacher $teacher) use ($faker){
            $teacher->user->username = $faker->safeEmail;
            $teacher->user->save();
        });

       \tcCore\OnboardingWizardReport::updateForAllTeachers();

        $this->assertCount(
            4,
            \tcCore\OnboardingWizardReport::all()
        );

    }

    /** @test */
    public function it_should_update_the_onboarding_reports_table_with_account_verified()
    {
        $this->assertCount(
            0,
            \tcCore\OnboardingWizardReport::all()
        );

        $faker = Faker\Factory::create();;
        $users = collect([]);
        Teacher::limit(4)->groupBy('user_id')->get()->each(function(Teacher $teacher) use ($faker, $users){
            $teacher->user->username = $faker->safeEmail;
            $teacher->user->save();
            $users->push($teacher->user);
        });
        $firstUser = $users->first();
        $firstUser->fill(['account_verified' => null]);
        $firstUser->save();



        \tcCore\OnboardingWizardReport::updateForAllTeachers();

        $this->assertCount(
            4,
            \tcCore\OnboardingWizardReport::all()
        );

        $users->each(function(User $user){
           $this->assertEquals($user->account_verified, \tcCore\OnboardingWizardReport::where('user_id',$user->getKey())->value('account_verified'));
        });


    }

}
