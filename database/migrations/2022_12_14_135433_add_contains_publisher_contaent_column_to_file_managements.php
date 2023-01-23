<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->boolean('contains_publisher_content')->nullable()->default(false);
        });

        \tcCore\FileManagement::where('typedetails', 'LIKE', '%"contains_publisher_content":"1"%')
            ->update(['contains_publisher_content' => true]);
    }

    public function down()
    {
        Schema::table('file_managements', function (Blueprint $table) {
            $table->dropColumn('contains_publisher_content');
        });
    }
};