<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BloomNullableToQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // because there is a enum column in the database and mySQL has no Idea what to do with this I set its mapping to
        // string so the compiler does not complain about it. Got the idea from https://github.com/doctrine/dbal/issues/3161
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('questions', function (Blueprint $table) {
            $table->string('bloom')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('bloom')->nullable(false)->change();
        });
    }
}
