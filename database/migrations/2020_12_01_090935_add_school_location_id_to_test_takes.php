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
        if(Schema::hasColumn('test_takes','school_location_id')){
            $this->down();
        }

        Schema::table('test_takes', function (Blueprint $table) {
            $table->unsignedInteger('school_location_id')->default(0)->index();
        });

        DB::statement('
                UPDATE test_takes set school_location_id = (select school_location_id from users where id = test_takes.user_id)
            ');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('school_location_id');
        });
    }
}
