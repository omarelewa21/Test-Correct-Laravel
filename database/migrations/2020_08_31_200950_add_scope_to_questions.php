<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use tcCore\Question;
use tcCore\Test;

class AddScopeToQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('scope')->nullable()->index();
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->string('scope')->nullable()->index();
        });

        Question::where('metadata','like','%cito%')->get()->each(function(Question $q){
            $q = $q->getQuestionInstance();
            $q->metadata = str_replace(['cito|','|cito','cito'],'',$q->metadata);
            $q->scope = 'cito';
            $q->save();
        });

        Test::where('metadata','like','%cito%')->get()->each(function(Test $t){
            $t->metadata = str_replace(['cito|','|cito','cito'],'',$t->metadata);
            $t->scope = 'cito';
            $t->save();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Question::where('scope','cito')->get()->each(function(Question $q){
            $q = $q->getQuestionInstance();
           if($q->metadata){
               $q->metadata .= '|cito';
           } else {
               $q->metadata = 'cito';
           }
           $q->save();
        });

        Test::where('scope','cito')->get()->each(function(Test $q){
            if($q->metadata){
                $q->metadata .= '|cito';
            } else {
                $q->metadata = 'cito';
            }
            $q->save();
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('scope');
        });

        Schema::table('tests', function (Blueprint $table) {
            $table->dropColumn('scope');
        });
    }
}
