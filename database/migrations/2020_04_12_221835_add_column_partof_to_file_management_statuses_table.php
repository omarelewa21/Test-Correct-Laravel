<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPartofToFileManagementStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('file_management_statuses', function (Blueprint $table) {
            $table->integer('partof');
            $table->string('colorcode');
        });

        \DB::table('file_management_statuses')->delete();

        \DB::table('file_management_statuses')->insert([
            [
                'id' => 1,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'Nieuw',
                'displayorder' => 1,
                'colorcode' => 'colorcode-41',
                'partof' => 1,
            ],
            [
                'id' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'In behandeling',
                'displayorder' => 2,
                'colorcode' => 'colorcode-42',
                'partof' => 2,
            ],
            [
                'id' => 3,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'Behandeling gepauzeerd',
                'displayorder' => 3,
                'colorcode' => 'colorcode-43',
                'partof' => 2,
            ],
            [
                'id' => 4,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'gereed voor eerste controle',
                'displayorder' => 4,
                'colorcode' => 'colorcode-44',
                'partof' => 2,
            ],
            [
                'id' => 5,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'gereed voor tweede controle',
                'displayorder' => 5,
                'colorcode' => 'colorcode-44',
                'partof' => 2,
            ],
            [
                'id' => 6,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'bijna klaar',
                'displayorder' => 6,
                'colorcode' => 'colorcode-45',
                'partof' => 2,
            ],
            [
                'id' => 7,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'goedgekeurd',
                'displayorder' => 7,
                'colorcode' => 'colorcode-46',
                'partof' => 7,
            ],
            [
                'id' => 8,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
                'name' => 'onvolledig, items ontbreken',
                'displayorder' => 8,
                'colorcode' => 'colorcode-47',
                'partof' => 8,
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
            $table->dropColumn(['partof','colorcode']);
        });
    }
}
