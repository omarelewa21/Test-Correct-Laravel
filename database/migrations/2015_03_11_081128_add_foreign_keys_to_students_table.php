<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToStudentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('students', function(Blueprint $table)
		{
			$table->foreign('class_id', 'fk_users_has_school_classes_school_classes1')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_users_has_school_classes_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('students', function(Blueprint $table)
		{
			$table->dropForeign('fk_users_has_school_classes_school_classes1');
			$table->dropForeign('fk_users_has_school_classes_users1');
		});
	}

}
