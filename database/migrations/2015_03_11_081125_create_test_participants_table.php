<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTestParticipantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('test_participants', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->timestamp('heartbeat_at')->nullable();
			$table->integer('test_take_id')->unsigned()->index('fk_test_takes_has_users_test_takes1_idx');
			$table->integer('user_id')->unsigned()->index('fk_test_takes_has_users_users1_idx');
			$table->integer('test_take_status_id')->unsigned()->index('fk_test_participants_test_take_statuses1_idx');
			$table->integer('school_class_id')->unsigned()->index('fk_test_participants_school_classes1_idx');
			$table->text('invigilator_note', 65535)->nullable();
			$table->decimal('rating', 4)->unsigned()->nullable();
			$table->decimal('retake_rating', 4)->unsigned()->nullable();
			$table->binary('ip_address', 16)->nullable();
			$table->unique(['test_take_id','user_id'], 'unique_test_takes_has_users');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('test_participants');
	}

}
