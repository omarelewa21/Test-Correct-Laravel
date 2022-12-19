<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionCompletionMulti extends FactoryQuestionCompletion
{
    /**
     * The question contains the answers:
     *
     * Answers are:
     *      surrounded by square brackets []
     *      divided by pipes |
     * The first answer is always the correct answer, the rest incorrect.
     *
     * answers are formatted like: [correct answer|incorrect answer1|incorrect 2]
     * @return string
     */
    public function questionDefinition()
    {
        return "<p>What is the color of grass [green|red|blue]. What is correct [test|car|fly]-correct</p>";
    }

    public function questionSubType(): string
    {
        return 'multi';
    }
}