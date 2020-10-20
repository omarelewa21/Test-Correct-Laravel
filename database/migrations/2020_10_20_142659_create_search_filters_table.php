<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSearchFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->text('filters')->nullable();
            $table->string('key');
            $table->string('name');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_filters');
    }
}
