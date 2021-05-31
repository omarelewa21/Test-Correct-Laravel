<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErrorMessagesCollumnToUwlsSoapResult extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('uwlr_soap_results', function (Blueprint $table) {
            $table->text('error_messages')->nullable();
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
            $table->removeColumn('error_messages');
        });
    }
}
