<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use tcCore\OpenQuestion;

class ChangeWiskundeQuestionToLongOpenQuestion extends Migration
{
    protected $tagName = 'voorheen-wiskunde-vraag';

    protected $extraTagName = 'wiskunde';

    protected function getTag($tagName = null)
    {
        if($tagName === null){
            $tagName = $this->tagName;
        }
        return \tcCore\Tag::where('name',$tagName)->first();
    }

    protected function getExtraTag()
    {
        return $this->getTag($this->extraTagName);
    }
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('tags', 'uuid')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->efficientUuid('uuid')->index()->unique()->nullable();
            });
        }

        $tag = $this->getTag();
        if($tag === null){
            $tag = \tcCore\Tag::create([
                'name' => $this->tagName
            ]);
        }

        if(!$extraTag = $this->getExtraTag()){
            $extraTag = \tcCore\Tag::create([
                'name' => $this->extraTagName
            ]);
        }

        $wiskundeQuestions = \tcCore\OpenQuestion::where('subtype','long')->get();

        // add tags to wiskunde questions
        $wiskundeQuestions->each(function(OpenQuestion $q) use ($tag, $extraTag){
            if(!$this->questionHasTag($q,$tag)) {
                $q->tags()->save($tag);
            }
            if(!$this->questionHasTag($q,$extraTag)){
                $q->tags()->save($extraTag);
            }
            $q->subtype = 'Long';
            $q->save();

        });

        // change subtype of all other medium questions to Long
        \tcCore\OpenQuestion::where('subtype','medium')->update(['subtype' => 'long']);
    }

    protected function questionHasTag(OpenQuestion $question, $tag){
        return (bool) $question->tags->first(function($t) use ($tag){
           return $t->id === $tag->id;
        });
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
