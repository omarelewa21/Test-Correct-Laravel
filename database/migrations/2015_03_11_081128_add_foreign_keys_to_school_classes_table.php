<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSchoolClassesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('school_classes', function(Blueprint $table)
		{
			$table->foreign('education_level_id', 'fk_classes_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('school_location_id', 'fk_classes_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('school_year_id', 'fk_classes_school_years1')->references('id')->on('school_years')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('mentor_id', 'fk_classes_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('manager_id', 'fk_classes_users2')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('school_classes', function(Blueprint $table)
		{
			$table->dropForeign('fk_classes_education_levels1');
			$table->dropForeign('fk_classes_school_locations1');
			$table->dropForeign('fk_classes_school_years1');
			$table->dropForeign('fk_classes_users1');
			$table->dropForeign('fk_classes_users2');
		});
	}

}
