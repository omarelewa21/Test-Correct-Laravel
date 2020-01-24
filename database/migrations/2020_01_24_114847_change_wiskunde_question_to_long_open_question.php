<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use tcCore\OpenQuestion;

class ChangeWiskundeQuestionToLongOpenQuestion extends Migration
{
    protected $tagName = 'voorheen-wiskunde-vraag';

    protected function getTag()
    {
     return \tcCore\Tag::where('name',$this->tagName)->first();
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tag = $this->getTag();
        if($tag === null){
            $tag = \tcCore\Tag::create([
                'name' => $this->tagName
            ]);
        }

        $wiskundeQuestions = \tcCore\OpenQuestion::where('subtype','long')->get();

        // add tags to wiskunde questions
        $wiskundeQuestions->each(function(OpenQuestion $q) use ($tag){
           $q->tags()->save($tag);
           $q->subtype = 'Long';
           $q->save();
        });

        // change subtype of all other medium questions to Long
        \tcCore\OpenQuestion::where('subtype','medium')->update(['subtype' => 'long']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        // set subtype to Medium
        \tcCore\OpenQuestion::where('subtype','long')->update(['subtype' => 'medium']);

        // find all questions with tag $this->tagName
        \tcCore\Tag::where('name',$this->tagName)->first()->questions->each(function($q){
            $q->subtype = 'long';
            $q->save();
        });

        $this->getTag()->forceDelete();

    }
}
