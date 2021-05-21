<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToOnboardingWizardReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->integer('nr_approved_test_files_365')->default(0)->after('nr_approved_test_files_90');
            $table->integer('nr_added_question_items_365')->default(0)->after('nr_added_question_items_90');
            $table->integer('nr_approved_classes_total')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_approved_classes_365')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_approved_classes_90')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_approved_classes_60')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_approved_classes_30')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_approved_classes_7')->default(0)->after('nr_added_question_items_total');
            $table->integer('nr_tests_taken_365')->default(0)->after('nr_tests_taken_90');
            $table->integer('nr_tests_checked_365')->default(0)->after('nr_tests_checked_90');
            $table->integer('nr_tests_rated_365')->default(0)->after('nr_tests_rated_90');
            $table->integer('nr_colearning_sessions_365')->default(0)->after('nr_colearning_sessions_90');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            $table->dropColumn([
                'nr_added_question_items_365','nr_approved_classes_7','nr_approved_classes_30','nr_approved_classes_60','nr_approved_classes_90','nr_approved_classes_365','nr_approved_classes_total','nr_tests_taken_365','nr_tests_checked_365','nr_tests_rated_365','nr_colearning_sessions_365',
                ]);
        });
    }
}
