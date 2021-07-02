<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSamlMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saml_messages', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('email')->nullable();
            $table->string('message_id');
            $table->string('eck_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saml_messages');
    }
}
