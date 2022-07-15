<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeWebsiteUrlInDemoTeacherRegistrationsNullable extends Migration
{
    public function up()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
            $table->string('website_url')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('demo_teacher_registrations', function (Blueprint $table) {
            $table->string('website_url')->nullable(false)->change();
        });
    }
}