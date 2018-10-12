<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart5Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tests', function(Blueprint $table)
        {
            $table->dropForeign('fk_tests_tests1');
        });

        Schema::table('tests', function(Blueprint $table)
        {
            $table->foreign('system_test_id', 'fk_tests_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('messages');
        Schema::drop('messages_recievers');
    }

}
