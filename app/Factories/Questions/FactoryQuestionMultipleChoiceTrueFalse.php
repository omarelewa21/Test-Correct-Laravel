<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionMultipleChoiceTrueFalse extends FactoryQuestionMultipleChoice
{
    //todo dont accept multiplechoice answers that are not true/false?
    //  True false question only should have Juist / Onjuist answers
    //  Only the score should be able to change.
    //      Or accept them, because false inputs need to be possible to create?

    protected function questionDefinition()
    {
        return "<p>Is Rotterdam de hoofdstad van Nederland?</p>\n";
    }

    protected function questionSubType()
    {
        return "TrueFalse";
    }

    protected function answerDefinition()
    {
        return [
            [
                'order' => 1,
                'answer' => 'Juist',
                'score' => 0,
            ],
            [
                'order' => 2,
                'answer' => 'Onjuist',
                'score' => 5,
            ],
        ];

    }
}