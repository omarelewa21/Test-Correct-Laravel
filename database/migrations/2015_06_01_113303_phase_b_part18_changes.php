<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart18Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->integer('number_of_teachers')->unsigned()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->dropColumn('number_of_teachers');
        });
    }
}
