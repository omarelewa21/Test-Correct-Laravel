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
            $table->renameColumn('os', 'platform');
            $table->renameColumn('user_os', 'platform_version');
            $table->string('platform_type')->nullable();
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
            $table->renameColumn('platform', 'os');
            $table->renameColumn('platform_version', 'user_os');
            $table->dropColumn('platform_type');
        });
    }
};
