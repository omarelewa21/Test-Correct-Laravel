<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\BaseSubject;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!BaseSubject::where('name', 'Anatomie en Fysiologie')->exists()) {
            $baseSubject = new BaseSubject();
            $baseSubject->id = 98;
            $baseSubject->name = 'Anatomie en Fysiologie Nova Haarlem';
            $baseSubject->show_in_onboarding = 0;
            $baseSubject->level = 'MBO';
            $baseSubject->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
