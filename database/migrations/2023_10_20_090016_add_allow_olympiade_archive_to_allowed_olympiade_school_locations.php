<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\FeatureSetting;
use tcCore\Question;
use tcCore\SchoolLocation;
use tcCore\Test;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // add feature allow_olympiade_archive to existing school locations with olympiade access
        FeatureSetting::where('title','allow_olympiade')->where('settingable_type',\tcCore\SchoolLocation::class)->get()->each(function (FeatureSetting $fs){
            $sl = SchoolLocation::find($fs->settingable_id);
            $sl->allow_olympiade_archive = true;
            $sl->save();
        });
        // make published_olympiade => published_olympiade_archive for both the tests and the questions
        Test::where('scope','published_olympiade')->update(['scope' => 'published_olympiade_archive']);
        Test::where('scope','not_published_olympiade')->update(['scope' => 'not_published_olympiade_archive']);

        Question::where('scope','published_olympiade')->update(['scope' => 'published_olympiade_archive']);
        Question::where('scope','not_published_olympiade')->update(['scope' => 'not_published_olympiade_archive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        FeatureSetting::where('settingable_type',SchoolLocation::class)->delete();

        // make published_olympiade => published_olympiade_archive for both the tests and the questions
        Test::where('scope','published_olympiade_archive')->update(['scope' => 'published_olympiade']);
        Test::where('scope','not_published_olympiade_archive')->update(['scope' => 'not_published_olympiade']);

        Question::where('scope','published_olympiade_archive')->update(['scope' => 'published_olympiade']);
        Question::where('scope','not_published_olympiade_archive')->update(['scope' => 'not_published_olympiade']);
    }
};
