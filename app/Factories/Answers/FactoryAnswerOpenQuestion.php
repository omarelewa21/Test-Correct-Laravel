<?php

namespace tcCore\Factories\Answers;

class FactoryAnswerOpenQuestion extends FactoryAnswer
{
    protected function setJsonAnswer()
    {
        $answer = 'Default answer text';

        return json_encode((object) ['value' => $answer]);
    }
}