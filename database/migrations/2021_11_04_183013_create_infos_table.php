<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infos', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index()->unique();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title_nl')->nullable();
            $table->string('title_en')->nullable();
            $table->text('content_nl')->nullable();
            $table->text('content_en')->nullable();
            $table->datetime('show_from');
            $table->datetime('show_until');
            $table->string('status')->default('INACTIVE');
            $table->integer('created_by');
            $table->boolean('for_all')->default(true);
        });

        Schema::create('info_role',function(Blueprint $table){
            $table->bigInteger('info_id');
            $table->bigInteger('role_id');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['info_id','role_id'],'info_role_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('infos');
        Schema::dropIfExists('info_roles');
    }
}
