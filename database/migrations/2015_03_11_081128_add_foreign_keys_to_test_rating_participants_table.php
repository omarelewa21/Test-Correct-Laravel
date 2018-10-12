<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTestRatingParticipantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_rating_participants', function(Blueprint $table)
		{
			$table->foreign('test_participant_id', 'fk_test_participants_has_test_ratings_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_rating_id', 'fk_test_participants_has_test_ratings_test_ratings1')->references('id')->on('test_ratings')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('test_rating_participants', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_participants_has_test_ratings_test_participants1');
			$table->dropForeign('fk_test_participants_has_test_ratings_test_ratings1');
		});
	}

}
