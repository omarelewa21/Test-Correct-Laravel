<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenShort extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'short';
    }

    public function attributeDefaults(): array
    {
        return [
            'spell_check_available' => false,
            'text_formatting'       => false,
            'mathml_functions'      => false,
            'restrict_word_amount'  => true,
            'max_words'             => 50,
        ];
    }
}