<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToQuestionGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('question_groups', function(Blueprint $table)
		{
			$table->foreign('database_question_id', 'fk_question_groups_database_questions1')->references('id')->on('database_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_id', 'fk_question_groups_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('question_groups', function(Blueprint $table)
		{
			$table->dropForeign('fk_question_groups_database_questions1');
			$table->dropForeign('fk_question_groups_tests1');
		});
	}

}
