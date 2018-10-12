<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('answers', function(Blueprint $table)
		{
			$table->foreign('question_id', 'fk_test_participants_has_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_participant_id', 'fk_test_participants_has_questions_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('answers', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_participants_has_questions_questions1');
			$table->dropForeign('fk_test_participants_has_questions_test_participants1');
		});
	}

}
