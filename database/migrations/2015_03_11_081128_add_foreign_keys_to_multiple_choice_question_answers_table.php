<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMultipleChoiceQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('multiple_choice_question_answers', function(Blueprint $table)
		{
			$table->foreign('multiple_choice_question_id', 'fk_multiple_choice_question_answers_multiple_choice_questions1')->references('id')->on('multiple_choice_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('multiple_choice_question_answers', function(Blueprint $table)
		{
			$table->dropForeign('fk_multiple_choice_question_answers_multiple_choice_questions1');
		});
	}

}
