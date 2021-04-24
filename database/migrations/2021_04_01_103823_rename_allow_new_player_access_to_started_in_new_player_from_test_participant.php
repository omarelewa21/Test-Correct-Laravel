<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameAllowNewPlayerAccessToStartedInNewPlayerFromTestParticipant extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_participants', function (Blueprint $table) {
            $table->renameColumn('allow_new_player_access','started_in_new_player');
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
            $table->renameColumn('started_in_new_player', 'allow_new_player_access');
        });
    }
}
