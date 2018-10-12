<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRolesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_roles', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->index('fk_user_has_roles_users1_idx');
			$table->integer('role_id')->unsigned()->index('fk_user_has_roles_roles1_idx');
			$table->timestamps();
			$table->softDeletes();
			$table->primary(['user_id','role_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_roles');
	}

}
