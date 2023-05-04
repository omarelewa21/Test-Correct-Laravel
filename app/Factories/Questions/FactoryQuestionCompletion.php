<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

abstract class FactoryQuestionCompletion extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    /**
     * @param array $answers
     * @return $this
     * @throws \Exception
     */
    public function addAnswers(array $answers)
    {
        throw new \Exception("Adding an answers to a completion question should be done inside the question text");
    }

    /**
     * @return string
     */
    public function answerDefinition()
    {
        return '';
    }

    public function questionType(): string
    {
        return 'CompletionQuestion';
    }
}