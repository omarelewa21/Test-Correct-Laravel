<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

class AddPrintscreenWindowOnTopEventTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    private static function createAndAdd($id, $name, $confirm, $reason) {
        TestTakeEventType::create([
            'id' => $id,
            'name' => $name,
            'requires_confirming' => $confirm,
            'reason' => $reason
        ]);
    }

    public function up()
    {
        TestTakeEventType::unguard();
        AddPrintscreenWindowOnTopEventTypes::createAndAdd(22, 'Screenshot', 1, 'printscreen');
        AddPrintscreenWindowOnTopEventTypes::createAndAdd(23, 'Other window on top', 1, 'other-window-on-top');
        TestTakeEventType::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('test_take_event_types')->where('reason', '=', 'printscreen')->delete();
        DB::table('test_take_event_types')->where('reason', '=', 'other-window-on-top')->delete();
    }
}
