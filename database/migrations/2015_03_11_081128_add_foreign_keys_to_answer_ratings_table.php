<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAnswerRatingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('answer_ratings', function(Blueprint $table)
		{
			$table->foreign('answer_id', 'fk_answer_ratings_answers1')->references('id')->on('answers')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_rating_id', 'fk_answer_ratings_test_ratings1')->references('id')->on('test_ratings')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_answer_ratings_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('answer_ratings', function(Blueprint $table)
		{
			$table->dropForeign('fk_answer_ratings_answers1');
			$table->dropForeign('fk_answer_ratings_test_ratings1');
			$table->dropForeign('fk_answer_ratings_users1');
		});
	}

}
