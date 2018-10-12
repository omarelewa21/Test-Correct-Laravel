<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTeachersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('teachers', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('user_id')->unsigned()->index('fk_users_has_school_classes_users2_idx');
			$table->integer('class_id')->unsigned()->index('fk_users_has_school_classes_school_classes2_idx');
			$table->integer('subject_id')->unsigned()->index('fk_teachers_subjects1_idx');
			$table->integer('school_year_id')->unsigned()->index('fk_teachers_school_years1_idx');
			$table->unique(['user_id','class_id','subject_id'], 'unique_user_has_school_classes_with_subject');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('teachers');
	}

}
