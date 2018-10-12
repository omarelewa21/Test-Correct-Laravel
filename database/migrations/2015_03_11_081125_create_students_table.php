<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateStudentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('students', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->index('fk_users_has_school_classes_users1_idx');
			$table->integer('class_id')->unsigned()->index('fk_users_has_school_classes_school_classes1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['user_id','class_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('students');
	}

}
