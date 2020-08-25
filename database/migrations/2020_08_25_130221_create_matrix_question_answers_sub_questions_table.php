<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatrixQuestionAnswersSubQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::create('matrix_question_answers_sub_questions', function (Blueprint $table) {
            $table->bigInteger('matrix_question_sub_question_id')->unsigned()->index('mqsqid-idx');
            $table->bigInteger('matrix_question_answer_id')->unsigned();
            $table->foreign('matrix_question_sub_question_id','fk_matrix_question_sub')->references('id')->on('matrix_question_sub_questions')->onUpdate('cascade')->onDelete('cascade');;
            $table->foreign('matrix_question_answer_id','fk_matrix_question_answer')->references('id')->on('matrix_question_answers')->onUpdate('cascade')->onDelete('cascade');;
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['matrix_question_sub_question_id', 'matrix_question_answer_id'], 'matrix_question_answer_sub_key');
        });
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('matrix_question_answers_sub_questions');
        Schema::enableForeignKeyConstraints();
    }
}
