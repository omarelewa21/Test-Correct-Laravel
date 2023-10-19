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
            $table->efficientUuid('uuid')->index()->unique();

            $table->boolean('shuffle')->default(false);
            $table->integer('selection_count')->nullable();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('relation_questions');
    }
};
