<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart9Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->string('customer_code', 60)->nullable()->after('user_id');
        });

        Schema::table('schools', function(Blueprint $table)
        {
            $table->string('customer_code', 60)->nullable()->after('umbrella_organization_id');
        });

        Schema::table('contacts', function(Blueprint $table)
        {
            $table->text('note', 65535)->nullable()->after('email');
        });

        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->decimal('epp', 6, 4)->unsigned()->nullable();
            $table->decimal('wanted_average', 6, 4)->unsigned()->nullable();
            $table->decimal('n_term', 6, 4)->nullable();
            $table->decimal('score_scale', 8, 4)->unsigned()->nullable();
        });

        //Cannot rename the column without just doing it in SQL, because test takes contains a enum field.
        DB::unprepared('ALTER TABLE `test_takes` CHANGE `normalization` `ppp` decimal(6,4) unsigned DEFAULT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('umbrella_organizations', function(Blueprint $table)
        {
            $table->drop('customer_code');
        });

        Schema::table('schools', function(Blueprint $table)
        {
            $table->drop('customer_code');
        });

        Schema::table('contacts', function(Blueprint $table)
        {
            $table->drop('note');
        });

        Schema::table('test_takes', function(Blueprint $table)
        {
            $table->drop('epp');
            $table->drop('wanted_average');
            $table->drop('n_term');
            $table->drop('score_scale');
        });

        //Cannot rename the column without just doing it in SQL, because test takes contains a enum field.
        DB::unprepared('ALTER TABLE `test_takes` CHANGE `ppp` `normalization` decimal(6,4) unsigned DEFAULT NULL');
    }
}
