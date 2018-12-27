<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('uri',255);
            $table->string('uri_full',255);
            $table->string('method');
            $table->text('request');
            $table->longText('response')->nullable();
            $table->text('headers');
            $table->integer('code')->default(-1);
            $table->string('ip',255);
            $table->float('duration');
            $table->string('user_id',36)->nullable();
            $table->string('user_agent',255)->nullable();
            $table->boolean('success')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('logs');
    }
}
