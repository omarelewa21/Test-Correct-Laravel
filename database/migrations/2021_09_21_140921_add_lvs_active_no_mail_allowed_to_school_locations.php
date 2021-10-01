<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLvsActiveNoMailAllowedToSchoolLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('lvs_active_no_mail_allowed')->default(false);

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
        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['lvs_active_no_mail_allowed']);
            //
        });
    }
}
