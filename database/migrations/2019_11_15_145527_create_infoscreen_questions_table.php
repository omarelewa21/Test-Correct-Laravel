<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInfoscreenQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infoscreen_questions', function (Blueprint $table) {
            $table->integer('id')->primary()->unsigned()->index('fk_infoscreen_questions_questions1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->string('subtype', 45)->nullable();
            $table->text('answer', 400)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('infoscreen_questions');
    }
}
