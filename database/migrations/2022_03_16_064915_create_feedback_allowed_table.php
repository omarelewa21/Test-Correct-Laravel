<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackAllowedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_allowed', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->boolean('is_allowed')->default(true);
            $table->timestamps();
        });

        DB::table('feedback_allowed')->insert(
            array(
                ['role_id' => 1]
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback_allowed');
    }
}
