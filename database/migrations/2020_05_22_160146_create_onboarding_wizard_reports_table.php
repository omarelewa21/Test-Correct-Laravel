<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnboardingWizardReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onboarding_wizard_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_name_first')->nullable();
            $table->string('user_name_suffix')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_created_at')->nullable();
            $table->string('user_last_login')->nullable();
            $table->string('school_location_name')->nullable();
            $table->string('school_location_customer_code')->nullable();
            $table->string('test_items_created_amount')->nullable();
            $table->string('tests_created_amount')->nullable();
            $table->string('first_test_planned_date')->nullable();
            $table->string('last_test_planned_date')->nullable();
            $table->string('first_test_created_date')->nullable();
            $table->string('last_test_created_date')->nullable();
            $table->string('first_test_taken_date')->nullable();
            $table->string('last_test_taken_date')->nullable();
            $table->string('tests_taken_amount')->nullable();
            $table->string('first_test_discussed_date')->nullable();
            $table->string('last_test_discussed_date')->nullable();
            $table->string('tests_discussed_amount')->nullable();
            $table->string('first_test_checked_date')->nullable();
            $table->string('last_test_checked_date')->nullable();
            $table->string('tests_checked_amount')->nullable();
            $table->string('first_test_rated_date')->nullable();
            $table->string('last_test_rated_date')->nullable();
            $table->string('tests_rated_amount')->nullable();
            $table->string('finished_demo_tour')->nullable();
            $table->string('finished_demo_steps_percentage')->nullable();
            $table->string('finished_demo_substeps_percentage')->nullable();
            $table->string('current_demo_tour_step')->nullable();
            $table->string('current_demo_tour_step_since_date')->nullable();
            $table->string('current_demo_tour_step_since_hours')->nullable();
            $table->string('average_time_finished_demo_tour_steps_hours')->nullable();
            $table->string('user_sections')->nullable();
            $table->string('user_login_amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onboarding_wizard_reports');
    }
}
