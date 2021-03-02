<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TestTakeStatusDuration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
            Schema::create('test_take_status_duration', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id')->nullable();  
            $table->integer('test_take_id')->nullable();   
            $table->integer('test_take_status')->nullable();   
            $table->integer('test_take_status_start')->nullable();   
            $table->integer('test_take_status_end')->nullable();   
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
         Schema::dropIfExists('test_take_status_duration');
    }
}
