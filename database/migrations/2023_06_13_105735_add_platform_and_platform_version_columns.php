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
            $table->string('platform')->nullable();
            $table->string('platform_version')->nullable();
        });
        DB::table('app_version_infos')
            ->update([
                'platform' => DB::raw('os'),
                'platform_version' => DB::raw('user_os')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('app_version_infos')
            ->update([
                'os' => DB::raw('platform'),
                'user_os' => DB::raw('platform_version')
            ]);

        Schema::table('app_version_infos', function (Blueprint $table) {
            $table->dropColumn(['platform','platform_version']);
        });
    }
};
