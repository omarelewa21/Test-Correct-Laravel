<?php

namespace tcCore\Factories\Answers;

use tcCore\MultipleChoiceQuestion;

class FactoryAnswerMultipleChoiceQuestion extends FactoryAnswer
{
    /**
     * @return false|string
     */
    protected function setJsonAnswer()
    {
        $returnAnswer = [];
        $possibleAnswers = [];

        foreach (MultipleChoiceQuestion::find($this->question->id)->multipleChoiceQuestionAnswers as $answer) {
            $possibleAnswers[$answer->id] = ($answer->score != 0 ? 1 : 0);
        }

        if($this->question->subtype == 'ARQ' || $this->question->subtype == 'TrueFalse'){
            // if too many are checked, set first answer 1 and the rest 0.
            if(collect($possibleAnswers)->sum() > 1){
                $count = 0;
                $possibleAnswers = collect($possibleAnswers)->map(function ($answer) use (&$count){
                    return ($count++ < 1 ? 1 : 0);
                })->toArray();
            }
        }
        return json_encode((object)$possibleAnswers);


        // all correct answers have a score of 1, incorrect are 0
        //TRUE FALSE
        //id 3007: {"3374":0,"3375":1}
        // 3375 has been checked (onjuist)

        //MULTIPLE CHOICE
        //id 3008: {"3376":0,"3377":1,"3378":1}
        // 3376 is not checked, 3377, 3378 are.
        //      Multiple choice allows multiple checked answers

        //ARQ
        //id 3009: {"3379":0,"3380":1,"3381":0,"3382":0,"3383":0}
        // 3380 has been checked, ARQ allows only one answer
    }
}