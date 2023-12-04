<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Word;

class RelationQuestion extends QuestionComponent
{
    public $answerStruct;
    public $questionStruct;

    protected function setAnswerStruct($question): void
    {
        $userIsStudent = ($this->answer && $this->answer->exists) ? true : false;

        if($question->shuffle && !$question->shuffle_per_participant) {
            //Caroussel the same for all students
            // get the answer struct from the test take
            $this->answerStruct = collect($this->testTake->testTakeRelationQuestions()->where('question_id', $question->id)->first()->json);
        } elseif(!$question->shuffle || !$userIsStudent) {
            //No caroussel, or teacher screen
            // get all words that are selected
            $this->answerStruct = $question->wordsToAsk()->keyBy('id');
        } else {
            //Caroussel per student
            // get the answer struct from the student answer
            $this->answerStruct = collect(json_decode($this->answer->json, true));
        }

        $this->questionStruct = Word::whereIn('id', $this->answerStruct->keys())->get()->keyBy('id');

        $this->answerStruct = $this->answerStruct->map(fn($value, $key) => $this->questionStruct[$key]->correctAnswerWord()->text);
    }
}