<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->integer('max_assessed_answer_index')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn('max_assessed_answer_index');
        });
    }
};
