<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnswersFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers_feedback', function (Blueprint $table) {
            $table->id();
            $table->integer('answer_id')->references('id')->on('answers');
            $table->integer('user_id')->references('id')->on('users');
            $table->string('message', 240);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('answers_feedback');
    }
}
