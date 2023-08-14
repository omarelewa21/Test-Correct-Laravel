<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionWriteDown extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'write';
    }

    public function attributeDefaults(): array
    {
        return [
            'spell_check_available' => false,
            'text_formatting'       => false,
            'mathml_functions'      => false,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ];
    }
}