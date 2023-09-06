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
        Schema::table('open_questions', function (Blueprint $table) {
            DB::statement('ALTER TABLE `open_questions` MODIFY `created_at` timestamp NULL;');
            DB::statement('ALTER TABLE `open_questions` MODIFY `updated_at` timestamp NULL;');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_questions', function (Blueprint $table) {
            //
        });
    }
};
