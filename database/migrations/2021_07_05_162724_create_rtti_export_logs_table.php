<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRttiExportLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rtti_export_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('test_take_id');
            $table->integer('user_id');
            $table->string('url');
            $table->text('export');
            $table->text('result')->nullable();
            $table->text('error')->nullable();
            $table->boolean('has_errors')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rtti_export_logs');
    }
}
