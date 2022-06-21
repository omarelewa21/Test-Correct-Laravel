<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

abstract class FactoryQuestionOpen extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    /**
     * @param string|array $answer "answer" | ["answer" => "text"]
     * @return
     */
    public function addAnswer($answer)
    {
        if (!$answer) {
            throw new \Exception("Adding an answer string or array ['answer' => 'text'] to the method addAnswers() is required.");
        }

        if (is_array($answer) && ($answer['answer'] ?? false)) {
            $this->questionProperties = array_merge($this->questionProperties, $answer);
        }
        if (is_string($answer)) {
            $this->questionProperties['answer'] = $answer;
        }

        return $this;
    }

    protected function definition()
    {
        return [
            "add_to_database" => 1,
            "answer" => "<p>voorbeeld antwoord: 3.14</p> ",
            "bloom" => "",
            "closeable" => 0,
            "decimal_score" => 0,
            "discuss" => 1,
            "maintain_position" => 0,
            "miller" => "",
            "is_open_source_content" => 0,
            "tags" => [
            ],
            "note_type" => "NONE",
            "order" => 0,
            "question" => '<p>voorbeeld vraag:</p> <p>wat is de waarde van pi</p> ',
            "rtti" => "",
            "score" => 5,
            "subtype" => $this->questionSubType(),
            "type" => "OpenQuestion",
            "attainments" => [
            ],
            "test_id" => 0,
            "all_or_nothing" => false,
        ];
    }

    protected abstract function questionSubType();
}