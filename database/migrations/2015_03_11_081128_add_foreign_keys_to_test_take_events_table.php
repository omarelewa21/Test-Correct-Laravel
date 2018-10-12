<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTestTakeEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_take_events', function(Blueprint $table)
		{
			$table->foreign('test_participant_id', 'fk_test_take_events_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_take_id', 'fk_test_take_events_test_takes1')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_take_event_type_id', 'fk_test_take_events_test_take_event_types1')->references('id')->on('test_take_event_types')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('test_take_events', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_take_events_test_participants1');
			$table->dropForeign('fk_test_take_events_test_takes1');
			$table->dropForeign('fk_test_take_events_test_take_event_types1');
		});
	}

}
