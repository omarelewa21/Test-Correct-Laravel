<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionWritingAssignment extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'writing';
    }
}