<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUuidToAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::beginTransaction();
        try {
            DB::statement('ALTER TABLE attachments ADD uuid binary(16)');
            \tcCore\Attachment::all()->each(function($attachment) {
                $attachment->uuid = $attachment->resolveUuid();
                $attachment->save();
            });
        } catch (Exception $e) {
            DB::rollBack();
        }
        DB::commit();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE attachments DROP uuid');
    }
}
