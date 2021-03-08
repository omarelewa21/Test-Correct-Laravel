<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

class AddControlKeyEventType extends Migration
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
        self::createAndAdd(24, 'Used unallowed Ctrl key combination', 1, 'ctrl-key');
        TestTakeEventType::reguard();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('test_take_event_types')->where('reason', '=', 'ctrl-key')->delete();
    }
}
