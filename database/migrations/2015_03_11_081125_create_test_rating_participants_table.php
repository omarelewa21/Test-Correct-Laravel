<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestRatingParticipantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_rating_participants', function(Blueprint $table)
		{
			$table->integer('test_participant_id')->unsigned()->index('fk_test_participants_has_test_ratings_test_participants1_idx');
			$table->integer('test_rating_id')->unsigned()->index('fk_test_participants_has_test_ratings_test_ratings1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['test_participant_id','test_rating_id'], 'primary_key_for_test_rating');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('test_rating_participants');
	}

}
