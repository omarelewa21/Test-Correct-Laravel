<?php

namespace tcCore\Http\Traits\Questions;

use Ramsey\Uuid\Uuid;
use tcCore\Exceptions\QuestionException;

trait WithQuestionDuplicating
{
    public function specificDuplication(array $attributes, $ignore = null): static
    {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            throw new QuestionException('Could not save parent question instance when duplicating.');
        }

        $question->fill($attributes);

        $question->setAttribute('uuid', Uuid::uuid4());

        if ($question->save() === false) {
            throw new QuestionException('Could not save specific question when duplicating.');
        }

        return $question;
    }
}