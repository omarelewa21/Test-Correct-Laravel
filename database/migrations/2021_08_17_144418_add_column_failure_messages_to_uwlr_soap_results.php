<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFailureMessagesToUwlrSoapResults extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uwlr_soap_results', function (Blueprint $table) {
            $table->text('failure_messages')->nullable();
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
           $table->dropColumn('failure_messages');
        });
    }
}
