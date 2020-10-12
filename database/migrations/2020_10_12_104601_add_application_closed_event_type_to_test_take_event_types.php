<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\testTakeEventType;

class AddApplicationClosedEventTypeToTestTakeEventTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $type = TestTakeEventType::where('id',9)->first();
        if(!$type) {
            TestTakeEventType::create([
               'id' => 9,
               'name' => 'Application closed',
               'requires_confirming' => 1
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
        Schema::table('test_take_event_types', function (Blueprint $table) {
            //
        });
    }
}
