<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionCompletionCompletion extends FactoryQuestionCompletion
{
    protected function definition()
    {
        return array_merge(
            parent::definition(),
            [
                'auto_check_incorrect_answer'      => false,
                'auto_check_answer_case_sensitive' => false,
            ]
        );
    }

    /**
     * The question contains the answers:
     *
     * Answers are surrounded by square brackets []
     * Answers are formatted like: [correct answer]
     *
     * @return string
     */
    public function questionDefinition(): string
    {
        return "<p>What is the color of grass [green]. What is the color of the sky? [blue].</p>";
    }

    public function questionSubType(): string
    {
        return 'completion';
    }
}