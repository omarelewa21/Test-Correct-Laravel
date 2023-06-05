<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->integer('assessing_answer_index')->after('assessing_question_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('assessing_answer_index');
        });
    }
};
