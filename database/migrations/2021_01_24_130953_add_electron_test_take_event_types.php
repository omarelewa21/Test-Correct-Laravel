<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

class AddElectronTestTakeEventTypes extends Migration
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
//        DB::table('test_take_event_types')->where('name', '=', $name)->update(['requires_confirming' => $confirm, 'reason' => $reason]);
    }

    public function up()
    {
        TestTakeEventType::unguard();
        AddElectronTestTakeEventTypes::createAndAdd(11, 'Pressed meta key', 1, 'before-input-meta');
        AddElectronTestTakeEventTypes::createAndAdd(12, 'Pressed alt key', 1, 'before-input-alt');
        AddElectronTestTakeEventTypes::createAndAdd(13,'Application closed alt+f4', 1, 'alt+f4');
        AddElectronTestTakeEventTypes::createAndAdd(14,'Lost focus blur', 1, 'blur');
        AddElectronTestTakeEventTypes::createAndAdd(15, 'Window hidden', 1, 'hide');
        AddElectronTestTakeEventTypes::createAndAdd(16, 'Window minimized', 1, 'minimize');
        AddElectronTestTakeEventTypes::createAndAdd(17,'Window moved', 1, 'move');
        AddElectronTestTakeEventTypes::createAndAdd(18, 'Window not fullscreen', 1, 'leave-full-screen');
        AddElectronTestTakeEventTypes::createAndAdd(19, 'Always on top changed', 1, 'always-on-top-changed');
        AddElectronTestTakeEventTypes::createAndAdd(20, 'Window resized', 1, 'resize');
        AddElectronTestTakeEventTypes::createAndAdd(21, 'Force shutdown', 1, 'session-end');
        TestTakeEventType::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('test_take_event_types')->where('name', '=', 'Pressed meta key')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Pressed alt key')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Application closed alt+f4')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Lost focus blur')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Window hidden')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Window minimized')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Window moved')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Window not fullscreen')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Always on top changed')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Window resized')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'Force shutdown')->delete();
    }
}
