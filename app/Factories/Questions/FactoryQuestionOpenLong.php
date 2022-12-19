<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenLong extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'medium'; //question Open-long == subtype medium
    }
}