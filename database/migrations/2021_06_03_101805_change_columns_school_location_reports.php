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
                'nr_approved_test_files_7','nr_approved_test_files_30','nr_approved_test_files_60','nr_approved_test_files_90','nr_approved_test_files_365','total_approved_files',
                'nr_approved_classes_7','nr_approved_classes_30','nr_approved_classes_60','nr_approved_classes_90','nr_approved_classes_365','total_approved_classes']);
            $table->integer('nr_activated_licenses_30')->before('nr_activated_licenses_90');
            $table->integer('nr_activated_licenses_60')->before('nr_activated_licenses_90');
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
