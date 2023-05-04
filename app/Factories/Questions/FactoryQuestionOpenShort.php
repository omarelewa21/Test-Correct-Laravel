<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenShort extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'short';
    }
}