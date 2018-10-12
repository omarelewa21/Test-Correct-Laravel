<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTeachersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('teachers', function(Blueprint $table)
		{
			$table->foreign('school_year_id', 'fk_teachers_school_years1')->references('id')->on('school_years')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('subject_id', 'fk_teachers_subjects1')->references('id')->on('subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('class_id', 'fk_users_has_school_classes_school_classes2')->references('id')->on('school_classes')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_users_has_school_classes_users2')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('teachers', function(Blueprint $table)
		{
			$table->dropForeign('fk_teachers_school_years1');
			$table->dropForeign('fk_teachers_subjects1');
			$table->dropForeign('fk_users_has_school_classes_school_classes2');
			$table->dropForeign('fk_users_has_school_classes_users2');
		});
	}

}
