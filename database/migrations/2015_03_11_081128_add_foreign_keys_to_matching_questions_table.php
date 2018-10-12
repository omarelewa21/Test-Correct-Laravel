<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMatchingQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('matching_questions', function(Blueprint $table)
		{
			$table->foreign('id', 'fk_matching_questions_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('matching_questions', function(Blueprint $table)
		{
			$table->dropForeign('fk_matching_questions_questions1');
		});
	}

}
