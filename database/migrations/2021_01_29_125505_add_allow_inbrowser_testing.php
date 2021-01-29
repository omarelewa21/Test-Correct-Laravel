<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowInbrowserTesting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_participants', function (Blueprint $table) {
            $table->boolean('allow_inbrowser_testing')->default(false);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('allow_inbrowser_testing')->default(false);
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
            $table->dropColumn(['allow_inbrowser_testing']);
        });

        Schema::table('school_locations', function (Blueprint $table) {
            $table->dropColumn(['allow_inbrowser_testing']);
        });
    }
}
