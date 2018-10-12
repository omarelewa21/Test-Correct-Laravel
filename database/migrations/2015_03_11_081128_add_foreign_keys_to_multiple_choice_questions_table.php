<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMultipleChoiceQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('multiple_choice_questions', function(Blueprint $table)
		{
			$table->foreign('id', 'fk_multiple_choice_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('multiple_choice_questions', function(Blueprint $table)
		{
			$table->dropForeign('fk_multiple_choice_questions_questions1');
		});
	}

}
