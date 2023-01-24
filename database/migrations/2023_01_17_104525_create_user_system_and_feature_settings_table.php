<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('user_system_settings')) {
            Schema::create('user_system_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('value');
            });
        }
        if (!Schema::hasTable('user_feature_settings')) {
            Schema::create('user_feature_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('value');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_system_settings');
        Schema::dropIfExists('user_feature_settings');
    }
};
