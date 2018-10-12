<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSchoolLocationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('school_locations', function(Blueprint $table)
		{
			$table->foreign('school_id', 'fk_school_location_school')->references('id')->on('schools')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('school_locations', function(Blueprint $table)
		{
			$table->dropForeign('fk_school_location_school');
		});
	}

}
