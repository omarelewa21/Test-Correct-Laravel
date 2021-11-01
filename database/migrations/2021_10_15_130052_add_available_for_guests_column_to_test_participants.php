<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvailableForGuestsColumnToTestParticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_participants', function (Blueprint $table) {
            $table->boolean('available_for_guests')->default(false);
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
            $table->dropColumn('available_for_guests');
        });
    }
}
