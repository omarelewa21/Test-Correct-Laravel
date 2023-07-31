<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers_feedback', function (Blueprint $table) {
            $table->string('thread_id')->nullable();
            $table->string('comment_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers_feedback', function (Blueprint $table) {
            $table->dropColumn('thread_id');
            $table->dropColumn('comment_id');
        });
    }
};
