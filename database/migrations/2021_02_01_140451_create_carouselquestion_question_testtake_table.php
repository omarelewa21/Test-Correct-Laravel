<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarouselquestionQuestionTesttakeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carouselquestion_subquestion_testtake', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('group_question_id');
            $table->bigInteger('group_question_question_id');
            $table->bigInteger('test_take_id');
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
        Schema::dropIfExists('carouselquestion_subquestion_testtake');
    }
}
