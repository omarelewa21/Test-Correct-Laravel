<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Attachment;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Attachment::where('type', 'video')
            ->whereNotNull('link')
            ->get()
            ->each(function ($attachment) {
                $attachment->update([
                    'link' => Attachment::convertYoutubeShortsLink($attachment->link)
                ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
