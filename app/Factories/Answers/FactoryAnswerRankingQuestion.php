<?php

namespace tcCore\Factories\Answers;

use tcCore\RankingQuestion;

class FactoryAnswerRankingQuestion extends FactoryAnswer
{
    protected function setJsonAnswer()
    {
        $possibleAnswers = [];
        //first get possible answers...
        foreach (RankingQuestion::find($this->question->id)->rankingQuestionAnswers as $answer){
            $possibleAnswers[$answer->id] = $answer->answer;
        }

        $count = 0;
        $returnAnswer = collect($possibleAnswers)->map(function ($answer, $key) use (&$count){
            return $count++;
        });

        // json given answer example:
        // {"570":0,"571":1,"572":2}

        // belongs to possible answers:
        // id: 570, answer: een (1)
        // id: 571, answer: twee (2)
        // id: 572, answer: drie (3)

        // correct answers (order) is based on order the answers are in the database?

        return json_encode((object) $returnAnswer);
    }
}