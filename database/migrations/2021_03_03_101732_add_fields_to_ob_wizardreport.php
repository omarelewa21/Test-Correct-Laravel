<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToObWizardreport extends Migration
{
  
   private $field_names = [
        'nr_approved_test_files_7',
        'nr_approved_test_files_30',
        'nr_approved_test_files_60',
        'nr_approved_test_files_90',
        'nr_approved_test_files_total',
        'nr_added_question_items_7',
        'nr_added_question_items_30',
        'nr_added_question_items_60',
        'nr_added_question_items_90',
        'nr_added_question_items_total',
        'nr_tests_taken_7',
        'nr_tests_taken_30',
        'nr_tests_taken_60',
        'nr_tests_taken_90',
        'nr_test_taken_total',
        'nr_tests_checked_7',
        'nr_tests_checked_30',
        'nr_tests_checked_60',
        'nr_tests_checked_90',
        'nr_tests_checked_total',
        'nr_tests_rated_7',
        'nr_tests_rated_30',
        'nr_tests_rated_60',
        'nr_tests_rated_90',
        'nr_tests_rated_total',
        'nr_colearning_sessions_7',
        'nr_colearning_sessions_30',
        'nr_colearning_sessions_60',
        'nr_colearning_sessions_90',
        'nr_colearning_sessions_total'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('onboarding_wizard_reports', function (Blueprint $table) {
            foreach($this->field_names as $field_name) {
                $table->integer($field_name)->nullable();
            }
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
            foreach($this->field_names as $field_name) {
               $table->dropColumn([$field_name]);
            }
        });
    }
}
