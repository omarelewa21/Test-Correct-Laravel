<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SchoolReportNamesChanged extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->renameColumn('total_activated_licenses','nr_activated_licenses_total');
            $table->renameColumn('total_uploaded_class_files','nr_uploaded_class_files_total');
            $table->renameColumn('total_uploaded_test_files','nr_uploaded_test_files_total');
            $table->renameColumn('total_added_question_items_files','nr_added_question_items_total');
            $table->renameColumn('total_tests_taken','nr_tests_taken_total');
            $table->renameColumn('total_tests_rated','nr_tests_rated_total');
            $table->renameColumn('total_colearning_sessions','nr_colearning_sessions_total');
            $table->renameColumn('total_unique_students_taken_test','nr_unique_students_taken_test_total');
            $table->renameColumn('total_participants_taken_test','nr_participants_taken_test_total');
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
            $table->renameColumn('nr_activated_licenses_total','total_activated_licenses');
            $table->renameColumn('nr_uploaded_class_files_total','total_uploaded_class_files');
            $table->renameColumn('nr_uploaded_test_files_total','total_uploaded_test_files');
            $table->renameColumn('nr_added_question_items_total','total_added_question_items_files');
            $table->renameColumn('nr_tests_taken_total','total_tests_taken');
            $table->renameColumn('nr_tests_rated_total','total_tests_rated');
            $table->renameColumn('nr_colearning_sessions_total','total_colearning_sessions');
            $table->renameColumn('nr_unique_students_taken_test_total','total_unique_students_taken_test');
            $table->renameColumn('nr_participants_taken_test_total','total_participants_taken_test');
        });
    }
}
