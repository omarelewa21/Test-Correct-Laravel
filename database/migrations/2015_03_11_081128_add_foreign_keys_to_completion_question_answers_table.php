<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCompletionQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('completion_question_answers', function(Blueprint $table)
		{
			$table->foreign('completion_question_id', 'fk_completion_question_answers_completion_questions1')->references('id')->on('completion_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('completion_question_answers', function(Blueprint $table)
		{
			$table->dropForeign('fk_completion_question_answers_completion_questions1');
		});
	}

}
