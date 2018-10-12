<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToInvigilatorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('invigilators', function(Blueprint $table)
		{
			$table->foreign('test_take_id', 'fk_test_takes_has_users_test_takes2')->references('id')->on('test_takes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_test_takes_has_users_user1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('invigilators', function(Blueprint $table)
		{
			$table->dropForeign('fk_test_takes_has_users_test_takes2');
			$table->dropForeign('fk_test_takes_has_users_user1');
		});
	}

}
