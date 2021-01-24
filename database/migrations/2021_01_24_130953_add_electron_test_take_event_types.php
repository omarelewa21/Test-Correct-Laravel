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
    private static function createAndAdd($name, $confirm, $reason) {
        TestTakeEventType::create(['name' => $name]);
        DB::table('test_take_event_types')->where('name', '=', $name)->update(['requires_confirming' => $confirm, 'reason' => $reason]);
    }

    public function up()
    {
        AddElectronTestTakeEventTypes::createAndAdd('Pressed meta key', 1, 'before-input-meta');
        AddElectronTestTakeEventTypes::createAndAdd('Pressed alt key', 1, 'before-input-alt');
        AddElectronTestTakeEventTypes::createAndAdd('Application closed alt+f4', 1, 'alt+f4');
        AddElectronTestTakeEventTypes::createAndAdd('Lost focus blur', 1, 'blur');
        AddElectronTestTakeEventTypes::createAndAdd('Window hidden', 1, 'hide');
        AddElectronTestTakeEventTypes::createAndAdd('Window minimized', 1, 'minimize');
        AddElectronTestTakeEventTypes::createAndAdd('Window moved', 1, 'move');
        AddElectronTestTakeEventTypes::createAndAdd('Window not fullscreen', 1, 'leave-full-screen');
        AddElectronTestTakeEventTypes::createAndAdd('Always on top changed', 1, 'always-on-top-changed');
        AddElectronTestTakeEventTypes::createAndAdd('Window resized', 1, 'resize');
        AddElectronTestTakeEventTypes::createAndAdd('Force shutdown', 1, 'session-end');
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
