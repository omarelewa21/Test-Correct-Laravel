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
//        Schema::table('app_version_infos', function (Blueprint $table) {
//            $table->string('user_os')->nullable();
//            $table->string('user_os_version')->nullable();
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_version_infos', function (Blueprint $table) {
            $table->dropColumn(['user_os','user_os_version']);
        });
    }
};
