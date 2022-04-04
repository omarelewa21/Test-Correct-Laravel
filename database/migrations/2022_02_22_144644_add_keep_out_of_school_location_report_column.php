<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\SchoolLocation;

class AddKeepOutOfSchoolLocationReportColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('keep_out_of_school_location_report')->default(0);
        });

        SchoolLocation::whereIn('customer_code',['BIT','MONITOR'])->update(['keep_out_of_school_location_report' => true]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['keep_out_of_school_location_report']);
        });
    }
}
