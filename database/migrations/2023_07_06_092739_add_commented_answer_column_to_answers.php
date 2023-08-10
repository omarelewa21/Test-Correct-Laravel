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
        if(!Schema::hasColumn('answers','commented_answer')) {
            DB::statement('ALTER TABLE `answers` 
                        MODIFY COlUMN `created_at` timestamp NULL,
                        MODIFY COLUMN `updated_at` timestamp NULL,
                        ADD COLUMN `commented_answer` TEXT NULL');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn('commented_answer');
        });
    }
};
