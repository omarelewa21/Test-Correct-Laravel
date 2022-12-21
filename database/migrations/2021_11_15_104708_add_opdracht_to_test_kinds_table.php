<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;
use tcCore\TestKind;

class AddOpdrachtToTestKindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('test_kinds', 'uuid')) {
            Schema::table('test_kinds', function (Blueprint $table) {
                $table->efficientUuid('uuid')->nullable();
            });

            TestKind::whereNull('uuid')->get()->each(function($testKind){
                $testKind->uuid = $testKind->resolveUuid();
                $testKind->save();
            });

//            Schema::table('test_kinds', function (Blueprint $table) {
//                $table->string('uuid')->nullable(false)->index()->change();;
//            });

        }


        \tcCore\TestKind::updateOrCreate(
            ['name' => 'Opdracht'],
            [
                'name'       => 'Opdracht',
                'has_weight' => 0,
                'uuid' =>  (new TestKind)->resolveUuid(),
            ],

        );
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
