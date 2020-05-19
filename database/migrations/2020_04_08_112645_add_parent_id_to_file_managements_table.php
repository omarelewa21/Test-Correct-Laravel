<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParentIdToFileManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->renameColumn('status','file_management_status_id');
        });

        Schema::table('file_managements', function (Blueprint $table) {
            $table->integer('file_management_status_id')->default(1)->charset('')->collation('')->change();
            $table->char('parent_id',36)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->renameColumn('file_management_status_id','status');
        });
    }
}
