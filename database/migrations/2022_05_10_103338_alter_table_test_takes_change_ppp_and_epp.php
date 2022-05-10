<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTestTakesChangePppAndEpp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->decimal('ppp',20,4)->change();
            $table->decimal('epp',20,4)->change();
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
            $table->decimal('ppp',6,4)->change();
            $table->decimal('epp',6,4)->change();
        });
    }
}
