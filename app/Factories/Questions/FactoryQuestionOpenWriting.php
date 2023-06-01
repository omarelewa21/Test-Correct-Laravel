<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionOpenWriting extends FactoryQuestionOpen
{
    public function questionSubType(): string
    {
        return 'writing';
    }

    public function attributeDefaults(): array
    {
        return [
            'spell_check_available' => true,
            'text_formatting'       => true,
            'mathml_functions'      => true,
            'restrict_word_amount'  => false,
            'max_words'             => null,
        ];
    }
}