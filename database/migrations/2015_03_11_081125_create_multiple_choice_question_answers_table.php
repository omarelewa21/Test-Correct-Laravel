<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMultipleChoiceQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('multiple_choice_question_answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('multiple_choice_question_id')->unsigned()->index('fk_multiple_choice_question_answers_multiple_choice_questio_idx');
			$table->string('answer')->nullable();
			$table->integer('order')->nullable();
			$table->integer('score')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('multiple_choice_question_answers');
	}

}
