<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use tcCore\TestTakeEventType;

class AddLostFocusAltTabEventToTestTakeEventTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (TestTakeEventType::find(10) === null) {
            DB::table('test_take_event_types')->insert([
                'id'                  => 10,
                'name'                => 'Lost focus alt tab',
                'requires_confirming' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        TestTakeEventType::find(10)->forceDelete();
    }
}
