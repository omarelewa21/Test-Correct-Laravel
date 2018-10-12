<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMatchingQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('matching_question_answers', function(Blueprint $table)
		{
			$table->foreign('matching_question_id', 'fk_matching_question_answers_matching_questions1')->references('id')->on('matching_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('correct_answer_id', 'fk_matching_question_answers_matching_question_answers1')->references('id')->on('matching_question_answers')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('matching_question_answers', function(Blueprint $table)
		{
			$table->dropForeign('fk_matching_question_answers_matching_questions1');
			$table->dropForeign('fk_matching_question_answers_matching_question_answers1');
		});
	}

}
