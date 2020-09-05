<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeQuestionAnswerFieldsToTextFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('completion_question_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });

        Schema::table('matching_question_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });

        Schema::table('matrix_question_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });

        Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });

        Schema::table('ranking_question_answers', function (Blueprint $table) {
            $table->text('answer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('completion_question_answers', function (Blueprint $table) {
            $table->string('answer')->nullable()->change();
        });

        Schema::table('matching_question_answers', function (Blueprint $table) {
            $table->string('answer')->nullable()->change();
        });

        Schema::table('matrix_question_answers', function (Blueprint $table) {
            $table->string('answer')->nullable()->change();
        });

        Schema::table('multiple_choice_question_answers', function (Blueprint $table) {
            $table->string('answer')->nullable()->change();
        });

        Schema::table('ranking_question_answers', function (Blueprint $table) {
            $table->string('answer')->nullable()->change();
        });
    }
}
