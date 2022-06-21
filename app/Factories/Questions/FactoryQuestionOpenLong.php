<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenLong extends FactoryQuestionOpen
{
    protected function questionSubType()
    {
        return 'medium'; //question Open-long == subtype medium
    }
}