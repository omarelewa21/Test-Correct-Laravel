<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestTakeEventType;

class AddLostFocusFraudEventShowStudentsColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->boolean('show_alarm_to_student');
        });

        TestTakeEventType::where("requires_confirming", "=", "1")->update(['show_alarm_to_student' => true]);
        TestTakeEventType::where("requires_confirming", "=", "0")->update(['show_alarm_to_student' => false]);
        TestTakeEventType::where("reason", "=", "vm")->update(['show_alarm_to_student' => false]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_take_event_types', function (Blueprint $table) {
            $table->dropColumn('show_alarm_to_student');
        });
    }
}
