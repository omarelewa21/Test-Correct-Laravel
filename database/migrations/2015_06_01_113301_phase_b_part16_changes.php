<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart16Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER TABLE `test_takes` CHANGE `score_scale` `pass_mark` DECIMAL(8,4)  UNSIGNED  NULL  DEFAULT NULL;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('ALTER TABLE `test_takes` CHANGE `pass_mark` `score_scale` DECIMAL(8,4)  UNSIGNED  NULL  DEFAULT NULL;');
    }
}
