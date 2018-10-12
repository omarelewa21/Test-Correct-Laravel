<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToRankingQuestionAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ranking_question_answers', function(Blueprint $table)
		{
			$table->foreign('ranking_question_id', 'fk_ranking_question_answers_ranking_questions1')->references('id')->on('ranking_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ranking_question_answers', function(Blueprint $table)
		{
			$table->dropForeign('fk_ranking_question_answers_ranking_questions1');
		});
	}

}
