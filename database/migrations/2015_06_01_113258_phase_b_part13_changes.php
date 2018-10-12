<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart13Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->integer('count_accounts')->unsigned()->default(0);
            $table->integer('count_active_licenses')->unsigned()->default(0);
            $table->integer('count_expired_licenses')->unsigned()->default(0);
            $table->date('count_last_test_taken')->nullable();
            $table->integer('count_licenses')->unsigned()->default(0);
            $table->integer('count_questions')->unsigned()->default(0);
            $table->integer('count_students')->unsigned()->default(0);
            $table->integer('count_teachers')->unsigned()->default(0);
            $table->integer('count_tests')->unsigned()->default(0);
            $table->integer('count_tests_taken')->unsigned()->default(0);

        });

        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->integer('count_active_licenses')->unsigned()->default(0);
            $table->integer('count_active_teachers')->unsigned()->default(0);
            $table->integer('count_expired_licenses')->unsigned()->default(0);
            $table->integer('count_licenses')->unsigned()->default(0);
            $table->integer('count_questions')->unsigned()->default(0);
            $table->integer('count_students')->unsigned()->default(0);
            $table->integer('count_teachers')->unsigned()->default(0);
            $table->integer('count_tests')->unsigned()->default(0);
            $table->integer('count_tests_taken')->unsigned()->default(0);
        });

        Schema::table('schools', function(Blueprint $table)
        {
            $table->integer('count_active_licenses')->unsigned()->default(0);
            $table->integer('count_active_teachers')->unsigned()->default(0);
            $table->integer('count_expired_licenses')->unsigned()->default(0);
            $table->integer('count_licenses')->unsigned()->default(0);
            $table->integer('count_questions')->unsigned()->default(0);
            $table->integer('count_students')->unsigned()->default(0);
            $table->integer('count_teachers')->unsigned()->default(0);
            $table->integer('count_tests')->unsigned()->default(0);
            $table->integer('count_tests_taken')->unsigned()->default(0);
        });

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->integer('count_active_licenses')->unsigned()->default(0);
            $table->integer('count_active_teachers')->unsigned()->default(0);
            $table->integer('count_expired_licenses')->unsigned()->default(0);
            $table->integer('count_licenses')->unsigned()->default(0);
            $table->integer('count_questions')->unsigned()->default(0);
            $table->integer('count_students')->unsigned()->default(0);
            $table->integer('count_teachers')->unsigned()->default(0);
            $table->integer('count_tests')->unsigned()->default(0);
            $table->integer('count_tests_taken')->unsigned()->default(0);
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
            $table->drop('count_accounts');
            $table->drop('count_active_licenses');
            $table->drop('count_expired_licenses');
            $table->drop('count_last_test_taken');
            $table->drop('count_licenses');
            $table->drop('count_questions');
            $table->drop('count_students');
            $table->drop('count_teachers');
            $table->drop('count_tests');
            $table->drop('count_tests_taken');

        });

        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->drop('count_active_licenses');
            $table->drop('count_active_teachers');
            $table->drop('count_expired_licenses');
            $table->drop('count_licenses');
            $table->drop('count_questions');
            $table->drop('count_students');
            $table->drop('count_teachers');
            $table->drop('count_tests');
            $table->drop('count_tests_taken');
        });

        Schema::table('schools', function(Blueprint $table)
        {
            $table->drop('count_active_licenses');
            $table->drop('count_active_teachers');
            $table->drop('count_expired_licenses');
            $table->drop('count_licenses');
            $table->drop('count_questions');
            $table->drop('count_students');
            $table->drop('count_teachers');
            $table->drop('count_tests');
            $table->drop('count_tests_taken');
        });

        Schema::table('school_locations', function(Blueprint $table)
        {
            $table->drop('count_active_licenses');
            $table->drop('count_active_teachers');
            $table->drop('count_expired_licenses');
            $table->drop('count_licenses');
            $table->drop('count_questions');
            $table->drop('count_students');
            $table->drop('count_teachers');
            $table->drop('count_tests');
            $table->drop('count_tests_taken');
        });
    }
}
