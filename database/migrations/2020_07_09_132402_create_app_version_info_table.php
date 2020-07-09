<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppVersionInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_version_infos', function (Blueprint $table) {
            $table->char('id',36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('user_id')->index();
            $table->string('version')->nullable();
            $table->string('os')->nullable();
            $table->text('headers')->nullable();
            $table->string('version_check_result')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_version_infos');
    }
}
