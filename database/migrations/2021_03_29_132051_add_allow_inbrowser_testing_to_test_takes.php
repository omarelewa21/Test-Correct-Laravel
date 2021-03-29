<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAllowInbrowserTestingToTestTakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
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
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('allow_inbrowser_testing');
        });
    }
}
