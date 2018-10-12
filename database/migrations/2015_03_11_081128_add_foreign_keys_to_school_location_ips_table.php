<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSchoolLocationIpsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('school_location_ips', function(Blueprint $table)
		{
			$table->foreign('school_location_id', 'fk_school_location_ips_school_locations1')->references('id')->on('school_locations')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('school_location_ips', function(Blueprint $table)
		{
			$table->dropForeign('fk_school_location_ips_school_locations1');
		});
	}

}
