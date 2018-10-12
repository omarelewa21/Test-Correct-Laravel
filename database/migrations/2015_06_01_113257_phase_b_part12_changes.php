<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart12Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->string('profile_image_name')->nullable();
            $table->integer('profile_image_size')->unsigned()->nullable();
            $table->string('profile_image_mime_type')->nullable();
            $table->string('profile_image_extension', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table)
        {
            $table->drop('profile_image_name');
            $table->drop('profile_image_size');
            $table->drop('profile_image_mime_type');
            $table->drop('profile_image_extension');
        });
    }
}
