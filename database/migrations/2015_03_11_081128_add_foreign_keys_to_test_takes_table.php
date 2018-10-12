<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTestTakesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('test_takes', function(Blueprint $table)
		{
			$table->foreign('period_id', 'fk_test_takes_periods1')->references('id')->on('periods')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_id', 'fk_test_takes_tests1')->references('id')->on('tests')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('retake_test_take_id', 'fk_test_takes_test_takes1')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('test_take_status_id', 'fk_test_takes_test_take_statuses1')->references('id')->on('test_take_statuses')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_test_takes_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('test_takes', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_takes_periods1');
			$table->dropForeign('fk_test_takes_tests1');
			$table->dropForeign('fk_test_takes_test_takes1');
			$table->dropForeign('fk_test_takes_test_take_statuses1');
			$table->dropForeign('fk_test_takes_users1');
		});
	}

}
