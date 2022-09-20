<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFeedbackMessageFieldToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers_feedback', function (Blueprint $table) {
            $table->text('message')->change();
        });
    }


    public function down()
    {
        Schema::table('answers_feedback', function (Blueprint $table) {
            $table->string('message',240)->change();
        });
    }
}
