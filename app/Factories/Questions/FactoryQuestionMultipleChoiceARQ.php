<?php

namespace tcCore\Factories\Questions;

class FactoryQuestionMultipleChoiceARQ extends FactoryQuestionMultipleChoice
{

    protected function questionDefinition()
    {
        return "<p>Assertion: The people of Belgium are dumb according to the people of the Netherlands</p>
                <p>Reason: Belgium's roads are in bad shape</p>";
        // answer is B, Assertion is valid, reason is in itself a valid statement,
        // but the reason is not the reason why the assertion is valid.
    }

    protected function calculateTotalScore()
    {
        return collect($this->questionProperties['answers'])->max('score');
    }

    protected function questionSubType()
    {
        return "ARQ";
    }

    protected function answerDefinition()
    {
        return [
            0 => [
                'answer' => '',
                'score' => '0',
            ],
            1 => [
                'answer' => '',
                'score' => '5',
            ],
            2 => [
                'answer' => '',
                'score' => '0',
            ],
            3 => [
                'answer' => '',
                'score' => '0',
            ],
            4 => [
                'answer' => '',
                'score' => '0',
            ],
        ];
    }
}