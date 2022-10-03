<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSpellCheckAvailableToDefaultFalseInOpenQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('open_questions', function (Blueprint $table) {
            $table->boolean('spell_check_available')->default(false)->change();
        });

        \tcCore\OpenQuestion::whereNull('deleted_at')->update(['spell_check_available' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('open_questions', function (Blueprint $table) {
            $table->boolean('spell_check_available')->nullable()->default(true)->change();
        });
    }
}
