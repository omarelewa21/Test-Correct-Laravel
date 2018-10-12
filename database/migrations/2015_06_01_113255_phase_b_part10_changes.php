<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PhaseBPart10Changes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function(Blueprint $table)
        {
            $table->enum('rtti', array('R', 'T1', 'T2', 'I'))->nullable()->after('note_type');
        });

        Schema::table('attachments', function(Blueprint $table)
        {
            $table->text('json')->nullable();
        });

        Schema::table('tag_relations', function(Blueprint $table) {
            $table->renameColumn('tag_relations_id', 'tag_relation_id');
            $table->renameColumn('tag_relations_type', 'tag_relation_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function(Blueprint $table)
        {
            $table->drop('rtti');
        });

        Schema::table('attachments', function(Blueprint $table)
        {
            $table->drop('json');
        });

        Schema::table('tag_relations', function(Blueprint $table) {
            $table->renameColumn('tag_relation_id', 'tag_relations_id');
            $table->renameColumn('tag_relation_type', 'tag_relations_type');
        });
    }
}
