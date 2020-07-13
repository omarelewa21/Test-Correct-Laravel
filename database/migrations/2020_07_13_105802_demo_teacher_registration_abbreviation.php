<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DemoTeacherRegistrationAbbreviation extends Migration
{    /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
       Schema::table('demo_teacher_registrations', function (Blueprint $table) {
           $table->string('abbreviation')->nullable();

       });
   }

   /**
    * Reverse the migrations.
    *
    * @return void
    */
   public function down()
   {
       Schema::table('demo_teacher_registrations', function (Blueprint $table) {
           $table->dropColumn('abbreviation');
       });
   }
}
