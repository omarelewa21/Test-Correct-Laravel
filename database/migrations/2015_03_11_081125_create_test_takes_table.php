<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestTakesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_takes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('user_id')->unsigned()->index('fk_test_takes_users1_idx');
			$table->integer('test_id')->unsigned()->index('fk_test_takes_tests1_idx');
			$table->integer('test_take_status_id')->unsigned()->index('fk_test_takes_test_take_statuses1_idx');
			$table->integer('period_id')->unsigned()->index('fk_test_takes_periods1_idx');
			$table->boolean('retake')->nullable();
			$table->integer('retake_test_take_id')->unsigned()->nullable()->index('fk_test_takes_test_takes1_idx');
			$table->dateTime('time_start')->nullable();
			$table->dateTime('time_end')->nullable();
			$table->string('location', 45)->nullable();
			$table->integer('weight')->unsigned()->nullable();
			$table->text('note', 65535)->nullable();
			$table->text('invigilator_note', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('test_takes');
	}

}
