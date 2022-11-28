<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEckidHashColumnToEckidUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eckid_user', function (Blueprint $table) {
            $table->string('eckid_hash')->index();
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eckid_user', function (Blueprint $table) {
            $table->dropColumn(['eckid_hash']);
        });
    }
}
