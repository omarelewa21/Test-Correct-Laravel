<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('relation_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::statement('ALTER TABLE relation_questions ADD uuid binary(16)');
    }

    public function down(): void
    {
        Schema::dropIfExists('relation_questions');
    }
};
