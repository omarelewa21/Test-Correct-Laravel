<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraStatusesToFileManagementStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_management_statuses', function (Blueprint $table) {
            //
        });

        \DB::table('file_management_statuses')->insert([
            [
                'id' => 9,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'Antwoordmodel ontbreekt',
                'displayorder' => 9,
                'colorcode' => 'colorcode-47',
                'partof' => 9,
            ],
            [
                'id' => 10,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'Meerdere toetsen aanwezig',
                'displayorder' => 10,
                'colorcode' => 'colorcode-47',
                'partof' => 10,
            ],
            [
                'id' => 11,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'Onduidelijke opdracht',
                'displayorder' => 11,
                'colorcode' => 'colorcode-47',
                'partof' => 11,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('file_management_statuses', function (Blueprint $table) {
            //
        });

        \tcCore\FileManagementStatus::whereIn('id',[9,10,11])->forceDelete();
    }
}
