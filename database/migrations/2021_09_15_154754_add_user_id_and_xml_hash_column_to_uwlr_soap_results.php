<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserIdAndXmlHashColumnToUwlrSoapResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uwlr_soap_results', function (Blueprint $table) {
            $table->string('username_who_imported')->default('system');
            $table->text('xml_hash')->nullable();
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
        Schema::table('uwlr_soap_results', function (Blueprint $table) {
            $table->dropColumn(['username_who_imported', 'xml_hash']);
        });
    }
}
