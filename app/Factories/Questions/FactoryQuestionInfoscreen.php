<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionInfoscreen extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    public function questionDefinition()
    {
        return "<p>Information screen (question)</p>";
    }

    public function answerDefinition()
    {
        return '';
    }

    public function questionType(): string
    {
        return 'InfoscreenQuestion';
    }

    public function questionSubType(): string
    {
        return 'info';
    }
}