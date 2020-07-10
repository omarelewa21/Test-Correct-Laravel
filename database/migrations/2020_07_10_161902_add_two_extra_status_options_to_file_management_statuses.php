<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use tcCore\FileManagementStatus;

class AddTwoExtraStatusOptionsToFileManagementStatuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        FileManagementStatus::where('displayorder','>=',8)->each(function($f){
           $f->displayorder++;
           $f->save();
        });
        \tcCore\FileManagementStatus::create([
           'id' => 12,
            'name' => 'Afgerond',
            'displayorder' => 8,
            'partof' => 12,
            'colorcode' => 'colorcode-46'
        ]);

        \tcCore\FileManagementStatus::create([
            'id' => 13,
            'name' => 'Geannuleerd',
            'displayorder' => 13,
            'partof' => 13,
            'colorcode' => 'colorcode-47'
        ]);

        Schema::table('file_management_statuses', function (Blueprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \tcCore\FileManagementStatus::whereIN('name',['Afgerond','Geannuleerd'])->forceDelete();
        FileManagementStatus::where('displayorder','>=',8)->each(function($f){
            $f->displayorder--;
            $f->save();
        });
        Schema::table('file_management_statuses', function (Blueprint $table) {
            //
        });
    }
}
