<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeLengthOfTestName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `tests` CHANGE `created_at` `created_at` timestamp NULL DEFAULT NULL');
        DB::statement('ALTER TABLE `tests` CHANGE `updated_at` `updated_at` timestamp NULL DEFAULT NULL;');
        Schema::table('tests', function (Blueprint $table) {
            $table->string('name',140)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            //
        });
    }
}
