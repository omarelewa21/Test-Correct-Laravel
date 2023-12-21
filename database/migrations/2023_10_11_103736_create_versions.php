<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();
            $table->string('name');

            $table->integer('versionable_id')->index();
            $table->string('versionable_type');

            $table->unsignedBigInteger('original_id')->nullable();
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('versions');
    }
};
