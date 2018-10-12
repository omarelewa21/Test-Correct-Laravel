<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTagRelationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tag_relations', function(Blueprint $table)
		{
			$table->foreign('tag_id', 'fk_tag_relations_tags1')->references('id')->on('tags')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tag_relations', function(Blueprint $table)
		{
			$table->dropForeign('fk_tag_relations_tags1');
		});
	}

}
