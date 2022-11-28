<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use tcCore\TestTakeEventType;

class AddRejoinedToTestTakeEventTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!TestTakeEventType::where('reason', 'rejoined')->exists()) {
            TestTakeEventType::create([
                'name'                => 'Rejoined',
                'requires_confirming' => 1,
                'reason'              => 'rejoined',
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
        DB::table('test_take_event_types')->where('reason', '=', 'rejoined')->delete();
    }
}
