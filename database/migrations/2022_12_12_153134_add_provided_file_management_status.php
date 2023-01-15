<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        \tcCore\FileManagementStatus::create([
            'name'         => 'Aangeleverd',
            'displayorder' => 0,
            'partof'       => 14,
            'colorcode'    => 'colorcode-2'
        ]);
    }

    public function down()
    {
        \tcCore\FileManagementStatus::whereName('Aangeleverd')->first()->delete();
    }
};