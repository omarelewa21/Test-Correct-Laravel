<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchoolClassesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('school_classes', function(Blueprint $table)
		{
			$table->increments('id');
			$table->timestamps();
			$table->softDeletes();
			$table->integer('school_location_id')->unsigned()->index('fk_classes_school_locations1_idx');
			$table->integer('education_level_id')->unsigned()->index('fk_classes_education_levels1_idx');
			$table->integer('school_year_id')->unsigned()->index('fk_classes_school_years1_idx');
			$table->integer('mentor_id')->unsigned()->index('fk_classes_users1_idx');
			$table->integer('manager_id')->unsigned()->index('fk_classes_users2_idx');
            $table->boolean('do_not_overwrite_from_interface')->nullable();
			$table->string('name', 45);
			$table->boolean('is_main_school_class')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('school_classes');
	}

}
