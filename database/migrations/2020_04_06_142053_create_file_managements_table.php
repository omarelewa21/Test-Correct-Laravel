<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileManagementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_managements', function (Blueprint $table) {
            $table->char('id',36)->primary();
            $table->integer('school_location_id');
            $table->integer('user_id');
            $table->string('origname');
            $table->string('name');
            $table->string('type');
            $table->text('typedetails')->nullable();
            $table->string('status')->default('new');
            $table->integer('handledby');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_managements');
    }
}
