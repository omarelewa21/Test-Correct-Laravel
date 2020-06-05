<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDemoToTestTakes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->boolean('demo')->default(false)->index('test_takes_demo_index');
        });

        \tcCore\Test::where('demo',true)->get()->each(function( $t){
           \tcCore\TestTake::where('test_id',$t->getKey())->update(['demo' => true]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_takes', function (Blueprint $table) {
            $table->dropColumn(['test_takes_demo_index']);
        });
    }
}
