<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchoolLocationIdToTestTakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
//            $table->unsignedInteger('school_location_id')->nullable()->default(0);
        });

        DB::statement('
                UPDATE test_takes set school_location_id = (select school_location_id from users where id = test_takes.user_id)
            ');

        Schema::table('test_takes', function (Blueprint $table) {
            $table->foreign('school_location_id')->references('id')->on('school_locations');
//            $table->unsignedInteger('school_location_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropForeign(['school_location_id']);
            $table->dropColumn('school_location_id');
        });
    }
}
