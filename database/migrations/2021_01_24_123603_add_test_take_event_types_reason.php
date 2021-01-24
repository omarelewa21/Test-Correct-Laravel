<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

class AddTestTakeEventTypesReason extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->string('reason', 50);
        });

        TestTakeEventType::where('name', '=', 'Start')->update(['reason' => 'start-test']);
        TestTakeEventType::where('name', '=', 'Stop')->update(['reason' => 'stop-test']);
        TestTakeEventType::where('name', '=', 'Lost focus')->update(['reason' => 'lost-focus']);
        TestTakeEventType::where('name', '=', 'Screenshot')->update(['reason' => 'screenshot']);
        TestTakeEventType::where('name', '=', 'Started late')->update(['reason' => 'started-late']);
        TestTakeEventType::where('name', '=', 'Start discussion')->update(['reason' => 'start-discussion']);
        TestTakeEventType::where('name', '=', 'End discussion')->update(['reason' => 'end-discussion']);
        TestTakeEventType::where('name', '=', 'Continue')->update(['reason' => 'continue']);
        TestTakeEventType::where('name', '=', 'Application closed')->update(['reason' => 'application-closed']);
        TestTakeEventType::where('name', '=', 'Lost focus alt tab')->update(['reason' => 'alt-tab']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
}
