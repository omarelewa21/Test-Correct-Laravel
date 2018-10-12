<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMatchingQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('matching_question_answers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('matching_question_id')->unsigned()->index('fk_matching_question_answers_matching_questions1_idx');
			$table->integer('correct_answer_id')->unsigned()->nullable()->index('fk_matching_question_answers_matching_question_answers1_idx');
			$table->string('answer')->nullable();
			$table->enum('type', array('LEFT','RIGHT'))->nullable();
			$table->integer('order')->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('matching_question_answers');
	}

}
