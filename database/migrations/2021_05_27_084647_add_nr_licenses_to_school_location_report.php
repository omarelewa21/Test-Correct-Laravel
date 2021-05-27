<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNrLicensesToSchoolLocationReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->integer('nr_licenses_365')->default(0)->after('nr_licenses');
            $table->integer('nr_licenses_90')->default(0)->after('nr_licenses');
            $table->integer('nr_activated_licenses_365')->default(0)->after('nr_activated_licenses');
            $table->integer('nr_activated_licenses_90')->default(0)->after('nr_activated_licenses');
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
