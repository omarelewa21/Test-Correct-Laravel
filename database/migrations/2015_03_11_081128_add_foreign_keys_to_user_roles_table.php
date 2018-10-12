<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->foreign('role_id', 'fk_user_has_roles_roles1')->references('id')->on('roles')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_user_has_roles_users1')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->dropForeign('fk_user_has_roles_roles1');
			$table->dropForeign('fk_user_has_roles_users1');
		});
	}

}
