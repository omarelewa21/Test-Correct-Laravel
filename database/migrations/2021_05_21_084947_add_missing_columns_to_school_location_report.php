<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToSchoolLocationReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->integer('nr_licenses_365')->default(0)->after('school_location_name');
            $table->integer('nr_licenses_90')->default(0)->after('school_location_name');
            $table->integer('nr_activated_licenses_90')->default(0)->after('nr_licenses');
            $table->integer('nr_activated_licenses_365')->default(0)->after('nr_activated_licenses_90');
            $table->integer('nr_approved_test_files_365')->default(0)->after('nr_approved_test_files_90');
            $table->integer('nr_added_question_items_365')->default(0)->after('nr_added_question_items_90');
            $table->integer('nr_approved_classes_365')->default(0)->after('nr_approved_classes_90');
            $table->integer('nr_tests_taken_365')->default(0)->after('nr_tests_taken_90');
            $table->integer('nr_tests_checked_365')->default(0)->after('nr_tests_checked_90');
            $table->integer('nr_tests_rated_365')->default(0)->after('nr_tests_rated_90');
            $table->integer('nr_colearning_sessions_365')->default(0)->after('nr_colearning_sessions_90');
            $table->integer('nr_unique_students_taken_test_365')->default(0)->after('total_colearning_sessions');
            $table->integer('nr_unique_students_taken_test_90')->default(0)->after('total_colearning_sessions');
            $table->integer('nr_unique_students_taken_test_60')->default(0)->after('total_colearning_sessions');
            $table->integer('nr_unique_students_taken_test_30')->default(0)->after('total_colearning_sessions');
            $table->integer('nr_unique_students_taken_test_7')->default(0)->after('total_colearning_sessions');
            $table->integer('total_unique_students_taken_test')->default(0)->after('total_colearning_sessions');

            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->dropColumn(['nr_licenses_365','nr_licenses_90','nr_activated_licenses_90','nr_activated_licenses_365','nr_approved_test_files_365',
                'nr_added_question_items_365','nr_approved_classes_365','nr_tests_taken_365','nr_tests_checked_365','nr_tests_rated_365','nr_colearning_sessions_365',
                'nr_unique_students_taken_test_365','nr_unique_students_taken_test_90','nr_unique_students_taken_test_60','nr_unique_students_taken_test_30',
                'nr_unique_students_taken_test_7','total_unique_students_taken_test']);
        });
    }
}
