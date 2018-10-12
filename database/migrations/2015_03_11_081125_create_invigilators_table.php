<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInvigilatorsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('invigilators', function(Blueprint $table)
		{
			$table->integer('test_take_id')->unsigned()->index('fk_test_takes_has_users_test_takes2_idx');
			$table->integer('user_id')->unsigned()->index('fk_test_takes_has_users_users2_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['test_take_id','user_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('invigilators');
	}

}
