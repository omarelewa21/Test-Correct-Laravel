<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('sales_organization_id', 'fk_users_sales_organizations1')->references('id')->on('sales_organizations')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('school_id', 'fk_users_schools1')->references('id')->on('schools')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('school_location_id', 'fk_users_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('fk_users_sales_organizations1');
			$table->dropForeign('fk_users_schools1');
			$table->dropForeign('fk_users_school_locations1');
		});
	}

}
