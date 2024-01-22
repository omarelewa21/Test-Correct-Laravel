<?php

namespace tcCore\Factories\Answers;

use Faker\Factory;

class FactoryAnswerRelationQuestion extends FactoryAnswer
{
    protected function setJsonAnswer()
    {
        $generator = Factory::create();
        $answer = [];
         foreach($this->question->createAnswerStruct() as $wordId => $option) {
            $answer[$wordId] = $generator->word;
         }

        return json_encode((object)$answer);
    }
}