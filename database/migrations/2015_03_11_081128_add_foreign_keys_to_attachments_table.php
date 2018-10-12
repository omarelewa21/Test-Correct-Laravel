<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAttachmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->foreign('question_id', 'fk_attachments_questions1')->references('id')->on('questions')->onUpdate('CASCADE')->onDelete('CASCADE');
			$table->foreign('question_group_id', 'fk_attachments_question_groups1')->references('id')->on('question_groups')->onUpdate('CASCADE')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('attachments', function(Blueprint $table)
		{
			$table->dropForeign('fk_attachments_questions1');
			$table->dropForeign('fk_attachments_question_groups1');
		});
	}

}
