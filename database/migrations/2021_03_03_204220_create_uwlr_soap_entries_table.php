<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUwlrSoapEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uwlr_soap_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uwlr_soap_result_id');
            $table->string('key');
            $table->string('object');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uwlr_soap_entries');
    }
}
