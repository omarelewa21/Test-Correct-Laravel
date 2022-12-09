<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_version_infos', function (Blueprint $table) {
            $table->string('os_version')->nullable();
            $table->string('os_platform')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_version_infos', function (Blueprint $table) {
            $table->dropColumn('os_version');
            $table->dropColumn('os_platform');
        });
    }
};
