<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHidAndVmEvent extends Migration
{
    private static function createAndAdd($id, $name, $confirm, $reason) {
        TestTakeEventType::create([
            'id' => $id,
            'name' => $name,
            'requires_confirming' => $confirm,
            'reason' => $reason
        ]);
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TestTakeEventType::unguard();
        AddHidAndVmEvent::createAndAdd(27, 'Forbidden device', 1, 'hid');
        AddHidAndVmEvent::createAndAdd(28, 'VM detected', 1, 'vm');
        TestTakeEventType::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('test_take_event_types')->where('name', '=', 'Forbidden device')->delete();
        DB::table('test_take_event_types')->where('name', '=', 'VM detected')->delete();
    }
}
