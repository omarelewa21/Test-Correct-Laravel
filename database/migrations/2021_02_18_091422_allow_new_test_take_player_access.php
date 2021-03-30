<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AllowNewTestTakePlayerAccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_participants', function (Blueprint $table) {
            $table->boolean('allow_new_player_access')->default(false);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_new_player_access')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_participants', function (Blueprint $table) {
            $table->dropColumn(['allow_new_player_access']);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['allow_new_player_access']);
        });
    }
}
