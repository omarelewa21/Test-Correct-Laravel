<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingVisibleExpirationColumnToTestTakeCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_take_codes', function (Blueprint $table) {
            $table->dateTime('rating_visible_expiration')->nullable()->after('prefix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_take_codes', function (Blueprint $table) {
            $table->dropColumn('rating_visible_expiration');
        });
    }
}
