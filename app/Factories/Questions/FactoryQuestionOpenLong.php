<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenLong extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'medium'; //question Open-long == subtype medium
    }

    public function attributeDefaults(): array
    {
        return [
            'spell_check_available' => false,
            'text_formatting'       => true,
            'mathml_functions'      => true,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ];
    }
}