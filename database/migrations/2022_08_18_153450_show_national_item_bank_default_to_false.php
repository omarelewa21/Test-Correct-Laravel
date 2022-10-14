<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShowNationalItemBankDefaultToFalse extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('school_locations','show_national_item_bank')) {
            Schema::table('school_locations', function (Blueprint $table) {
                $table->dropColumn('show_national_item_bank');
            });
        }
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('show_national_item_bank')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('school_locations','show_national_item_bank')) {
            Schema::table('school_locations', function (Blueprint $table) {
                $table->dropColumn('show_national_item_bank');
            });
        }
        Schema::table('school_locations', function (Blueprint $table) {
            $table->boolean('show_national_item_bank')->default(true);
        });
    }
}
