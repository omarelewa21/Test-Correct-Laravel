<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShortcodeClicksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shortcode_clicks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('shortcode_id');
            $table->string('ip');
            $table->uuid('uuid');
            $table->integer('user_id')->nullable(); // user_id of the user who is created through this process
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shortcode_clicks');
    }
}
