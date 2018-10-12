<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtendAttainments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attainments', function(Blueprint $table)
        {
            $table->integer('attainment_id')->unsigned()->nullable()->index('fk_attainments_attainments1_idx')->after('education_level_id');
            $table->string('subcode', 45)->nullable()->after('code');
            $table->foreign('attainment_id', 'fk_attainments_attainments1')->references('id')->on('attainments')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attainments', function(Blueprint $table)
        {
            $table->dropForeign('fk_attainments_attainments1');
            $table->dropIndex('fk_attainments_attainments1_idx');
            $table->dropColumn('subcode');
            $table->dropColumn('attainment_id');
        });
    }
}
