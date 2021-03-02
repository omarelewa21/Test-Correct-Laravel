<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestTakeStatusLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
            Schema::create('test_take_status_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id')->nullable();  
            $table->integer('test_take_id')->nullable();   
            $table->integer('test_take_status')->nullable();   
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
         Schema::dropIfExists('test_take_status_log');
    }
}
