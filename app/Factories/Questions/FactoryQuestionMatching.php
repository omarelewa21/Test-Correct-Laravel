<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

abstract class FactoryQuestionMatching extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    /**
     * @param array $answers
     * @return $this
     */
    public function addAnswers(array $answers)
    {
        if (!$answers) {
            throw new \Exception("Adding an matchingQuestionAnswers data array to the method addAnswers() is required");
        }

        $this->questionProperties['answers'] = $answers;

        return $this;
    }

    protected function definition()
    {
        //generate default question, with default answers, override answers later if provided.
        return array_merge(
            parent::definition(),
            [
                "answers" => $this->answerDefinition(),
            ]
        );
    }

    public function questionType(): string
    {
        return 'MatchingQuestion';
    }
}