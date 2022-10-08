<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledByColumnToTestTakesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->unsignedInteger('scheduled_by')->nullable();
            $table->foreign('scheduled_by')->references('id')->on('users');
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
            $table->dropForeign(['scheduled_by']);
            $table->dropColumn('scheduled_by');
        });
    }
}
