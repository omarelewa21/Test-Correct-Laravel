<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDemoTeacherRegistrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demo_teacher_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_location');
            $table->string('website_url');
            $table->string('address');
            $table->string('postcode');
            $table->string('city');
            $table->string('gender');
            $table->string('gender_different')->nullable();
            $table->string('name_first');
            $table->string('name_suffix')->nullable();
            $table->string('name');
            $table->string('username');
            $table->text('subjects');
            $table->string('mobile');
            $table->text('remarks')->nullable();
            $table->text('how_did_you_hear_about_test_correct')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demo_teacher_registrations');
    }
}
