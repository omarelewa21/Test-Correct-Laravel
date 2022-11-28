<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportProgressCollumnToUwlrSoapEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uwlr_soap_entries', function (Blueprint $table) {
            $table->string('import_progress')->nullable();
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('uwlr_soap_entries', function (Blueprint $table) {
            $table->dropColumn(['import_progress']);
            //
        });
    }
}
