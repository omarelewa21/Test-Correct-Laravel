<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUwlrSoapResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uwlr_soap_results', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('client_code');
            $table->string('client_name');
            $table->string('school_year');
            $table->string('brin_code');
            $table->string('xsdversie');
            $table->string('dependance_code');
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
        Schema::dropIfExists('uwlr_soap_results');
    }
}
