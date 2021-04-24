<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitoExportRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cito_export_rows', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('brin')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('vak')->nullable();
            $table->string('leerdoel')->nullable();
            $table->datetime('answered_at')->nullable();
            $table->string('item_1')->nullable();
            $table->string('item_2')->nullable();
            $table->string('item_3')->nullablee();
            $table->string('item_4')->nullable();
            $table->string('item_5')->nullable();
            $table->string('item_6')->nullable();
            $table->string('item_7')->nullable();
            $table->string('item_8')->nullable();
            $table->string('item_9')->nullable();
            $table->string('item_10')->nullable();
            $table->string('item_11')->nullable();
            $table->string('item_12')->nullable();
            $table->string('item_13')->nullable();
            $table->string('item_14')->nullable();
            $table->string('item_15')->nullable();
            $table->string('item_16')->nullable();

            $table->text('answer_1')->nullable();
            $table->text('answer_2')->nullable();
            $table->text('answer_3')->nullable();
            $table->text('answer_4')->nullable();
            $table->text('answer_5')->nullable();
            $table->text('answer_6')->nullable();
            $table->text('answer_7')->nullable();
            $table->text('answer_8')->nullable();
            $table->text('answer_9')->nullable();
            $table->text('answer_10')->nullable();
            $table->text('answer_11')->nullable();
            $table->text('answer_12')->nullable();
            $table->text('answer_13')->nullable();
            $table->text('answer_14')->nullable();
            $table->text('answer_15')->nullable();
            $table->text('answer_16')->nullable();

            $table->integer('score_1')->nullable();
            $table->integer('score_2')->nullable();
            $table->integer('score_3')->nullable();
            $table->integer('score_4')->nullable();
            $table->integer('score_5')->nullable();
            $table->integer('score_6')->nullable();
            $table->integer('score_7')->nullable();
            $table->integer('score_8')->nullable();
            $table->integer('score_9')->nullable();
            $table->integer('score_10')->nullable();
            $table->integer('score_11')->nullable();
            $table->integer('score_12')->nullable();
            $table->integer('score_13')->nullable();
            $table->integer('score_14')->nullable();
            $table->integer('score_15')->nullable();
            $table->integer('score_16')->nullable();
            $table->string('question_type')->nullable();
            $table->integer('test_take_id')->nullable();
            $table->integer('test_participant_id')->nullable();
            $table->integer('question_id')->nullable();
            $table->integer('answer_id')->nullable();
            $table->text('json')->nullable();
            $table->integer('number')->nullable();
            $table->boolean('export')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cito_export_rows');
    }
}
