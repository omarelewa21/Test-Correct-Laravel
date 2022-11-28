<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyidToSchoollocationAndRReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->string('company_id')->default('');
        });
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->string('company_id')->default('')->after('school_location_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
        Schema::table('school_location_reports', function (Blueprint $table) {
            $table->dropColumn('company_id');
        });
    }
}
