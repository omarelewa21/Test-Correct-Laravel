<?php

namespace tcCore\Factories\Answers;

use tcCore\CompletionQuestion;

class FactoryAnswerCompletionQuestion extends FactoryAnswer
{
    protected function setJsonAnswer()
    {
        $possibleAnswers = [];
        $returnAnswer = [];

        foreach (CompletionQuestion::find($this->question->id)->completionQuestionAnswers as $answer) {
            $possibleAnswers[$answer->tag][] = [$answer->answer => $answer->correct];
        }

        //multi: for each tag, one question has to be picked.
        //single: for each tag, a random word, or the correct answer has to be picked

        if ($this->question->subtype == 'completion') {
            foreach ($possibleAnswers as $tag => $answersArray) {
                $returnAnswer[] = array_key_first($answersArray[rand(0, count($answersArray) - 1)]);
            }
            return json_encode((object)$returnAnswer);
        }
        //for some reason multi answers start at index 1 instead of 0, while single start at 0.
        foreach ($possibleAnswers as $tag => $answersArray) {
            $returnAnswer[$tag] = array_key_first($answersArray[rand(0, count($answersArray) - 1)]);
        }

        return json_encode((object)$returnAnswer);

        //result examples of answers: (completion vs multi completion)

        // single id 3010, answer json: {"0":"green"}
        // single id 2287, answer json: {"0":"een","1":"groen"}

        // multi id 3011, answer json: {"1":"green"}
        // multi id 2286, answer json: {"1":"1"}
    }
}