<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTestsDiscussedCounter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->integer('count_tests_discussed')->unsigned()->default(0)->after('count_tests_taken');
        });

        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->boolean('is_discussed')->default(0)->after('period_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->drop('count_tests_discussed');
        });

        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->drop('is_discussed');
        });
    }
}
