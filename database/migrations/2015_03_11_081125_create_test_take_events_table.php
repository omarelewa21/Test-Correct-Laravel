<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTakeEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_take_events', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('test_take_id')->unsigned()->index('fk_test_take_events_test_takes1_idx');
			$table->integer('test_participant_id')->unsigned()->nullable()->index('fk_test_take_events_test_participants1_idx');
			$table->integer('test_take_event_type_id')->unsigned()->index('fk_test_take_events_test_take_event_types1_idx');
			$table->boolean('confirmed')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('test_take_events');
	}

}
