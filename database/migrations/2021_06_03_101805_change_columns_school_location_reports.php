<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnsSchoolLocationReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->dropColumn(['nr_licenses_90','nr_licenses_365','nr_activated_licenses_365','nr_browsealoud_licenses',
                'nr_approved_test_files_7','nr_approved_test_files_30','nr_approved_test_files_60','nr_approved_test_files_90','nr_approved_test_files_365','total_approved_test_files',
                'nr_approved_classes_7','nr_approved_classes_30','nr_approved_classes_60','nr_approved_classes_90','nr_approved_classes_365','total_approved_classes',
                'nr_tests_checked_7','nr_tests_checked_30','nr_tests_checked_60','nr_tests_checked_90','nr_tests_checked_365','total_tests_checked']);
            $table->integer('nr_activated_licenses_60')->after('nr_licenses');
            $table->integer('nr_activated_licenses_30')->after('nr_licenses');
            $table->integer('total_uploaded_test_files')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_test_files_365')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_test_files_90')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_test_files_60')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_test_files_30')->after('nr_activated_licenses');
            $table->integer('total_uploaded_class_files')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_class_files_365')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_class_files_90')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_class_files_60')->after('nr_activated_licenses');
            $table->integer('nr_uploaded_class_files_30')->after('nr_activated_licenses');
            $table->integer('nr_participants_taken_test_7');
            $table->integer('nr_participants_taken_test_30');
            $table->integer('nr_participants_taken_test_60');
            $table->integer('nr_participants_taken_test_90');
            $table->integer('nr_participants_taken_test_365');
            $table->integer('total_participants_taken_test');
            $table->renameColumn('nr_activated_licenses','total_activated_licenses');
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
            //
        });
    }
}
