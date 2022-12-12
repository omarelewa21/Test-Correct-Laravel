<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpdrachtToTestKindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        Schema::table('test_kinds', function (Blueprint $table) {
//            //
//        });
        DB::statement('ALTER TABLE test_kinds ADD uuid binary(16)');

        \tcCore\TestKind::create([
            'name'       => 'Opdracht',
            'has_weight' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('test_kinds', function (Blueprint $table) {
//            //
//        });
        \tcCore\TestKind::where([
            'name'       => 'Opdracht',
            'has_weight' => 0,
        ])->forceDelete();
    }
}
