<?php

namespace tcCore\Factories\Answers;

class FactoryAnswerInfoscreenQuestion extends FactoryAnswer
{
    protected function setJsonAnswer() : string
    {
        return json_encode("seen");
    }
}