<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTestParticipantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_participants', function(Blueprint $table)
		{
			$table->foreign('school_class_id', 'fk_test_participants_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_take_status_id', 'fk_test_participants_test_take_statuses1')->references('id')->on('test_take_statuses')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_take_id', 'fk_test_takes_has_users_test_takes1')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_test_takes_has_users_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('test_participants', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_participants_school_classes1');
			$table->dropForeign('fk_test_participants_test_take_statuses1');
			$table->dropForeign('fk_test_takes_has_users_test_takes1');
			$table->dropForeign('fk_test_takes_has_users_users1');
		});
	}

}
