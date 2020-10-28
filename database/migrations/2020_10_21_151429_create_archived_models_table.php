<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archived_models', function (Blueprint $table) {
            $table->id();
            $table->integer('archivable_model_id')->index();
            $table->string('archivable_model_type');
            $table->integer('user_id')->index();
            $table->timestamps();
            $table->index(['archivable_model_id', 'archivable_model_type','user_id'],'model_id_and_type_user_id_index');
            $table->index(['archivable_model_type','user_id'],'archivable_model_type_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archived_models');
    }
}
