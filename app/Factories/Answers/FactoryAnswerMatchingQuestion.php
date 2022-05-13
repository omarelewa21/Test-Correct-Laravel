<?php

namespace tcCore\Factories\Answers;

class FactoryAnswerMatchingQuestion extends FactoryAnswer
{
    protected function setJsonAnswer()
    {
        $answers = $this->question->matchingQuestionAnswers
            ->reduce(function($carry, $answer) {

                if (!$answer->correct_answer_id){
                    return $carry;
                }

                return $carry + [$answer->id => (string) $answer->correct_answer_id];
            }, []);

//        dd(json_encode((object)$answers));


        // id 3012: matching
        // id 3013: classify

        // 3012 matching : {"4195":"4194","4197":"4196","4199":"4198"}
        // {RIGHT:LEFT, }  right is $key, left is $value
        // left is passive, right gets placed in at the correct left value

        // 4194 LEFT car,       4196 LEFT spaghetti,    4198 LEFT computer
        // 4195 RIGHT wheels,   4197 RIGHT meatballs,   4199 RIGHT monitor

        // 3013 classify :
        // {"4202":"4200","4201":"4200","4203":"4200","4205":"4204","4208":"4206","4207":"4206"}
        // Left is the classification, right are the possible answers

        // each left requires atleast one right.
        // all right's need to be divided among the lefts

        return json_encode((object) $answers);
    }
}