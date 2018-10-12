<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnswerRatingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('answer_ratings', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('answer_id')->unsigned()->index('fk_answer_ratings_answers1_idx');
			$table->integer('user_id')->unsigned()->index('fk_answer_ratings_users1_idx');
			$table->integer('test_rating_id')->unsigned()->nullable()->index('fk_answer_ratings_test_ratings1_idx');
			$table->decimal('rating', 11, 1)->unsigned()->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('answer_ratings');
	}

}
