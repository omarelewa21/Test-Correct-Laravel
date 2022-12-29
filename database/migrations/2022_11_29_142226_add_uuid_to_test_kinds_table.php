<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\TestKind;

return new class extends Migration {
    public function up()
    {
        DB::statement('ALTER TABLE test_kinds ADD uuid binary(16)');
        TestKind::all()->each(function ($testKind) {
            $testKind->uuid = $testKind->resolveUuid();
            $testKind->save();
        });
    }

    public function down()
    {
        Schema::table('test_kinds', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};