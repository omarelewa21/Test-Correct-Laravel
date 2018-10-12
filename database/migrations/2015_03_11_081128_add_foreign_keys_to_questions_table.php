<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('questions', function(Blueprint $table)
		{
			$table->foreign('database_question_id', 'fk_questions_database_questions1')->references('id')->on('database_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_group_id', 'fk_questions_question_groups1')->references('id')->on('question_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_id', 'fk_questions_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('questions', function(Blueprint $table)
		{
			$table->dropForeign('fk_questions_database_questions1');
			$table->dropForeign('fk_questions_question_groups1');
			$table->dropForeign('fk_questions_tests1');
		});
	}

}
