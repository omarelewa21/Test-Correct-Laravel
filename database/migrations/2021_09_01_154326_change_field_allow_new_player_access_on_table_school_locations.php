<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldAllowNewPlayerAccessOnTableSchoolLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_locations', function(Blueprint $table) {
            $table->boolean('allow_new_player_access')->default(2)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_locations', function(Blueprint $table) {
            $table->boolean('allow_new_player_access')->default(0)->change();
        });
    }
}
