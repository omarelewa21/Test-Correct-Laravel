<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionCompletionMulti extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    /**
     * @param array $answers
     * @return $this
     */
    public function addAnswers(array $answers)
    {
       throw new \Exception("Adding an answers to a completion question should be done inside the question text");
    }

    protected function definition()
    {
        return [
            "add_to_database" => 1,
            "bloom" => "",
            "answer" => "",
            "closeable" => 0,
            "decimal_score" => 0,
            "discuss" => 1,
            "maintain_position" => 0,
            "miller" => "",
            "is_open_source_content" => 0,
            "tags" => [],
            "note_type" => "NONE",
            "order" => 0,
            "rtti" => "",
            "subtype" => "multi",
            "type" => "CompletionQuestion",
            "attainments" => [],
            "all_or_nothing" => false,
            "score" => 5,
            //The following can be overridden in child classes
            "question" => $this->questionDefinition(),
            //The following need to be calculated/set before saving
            "test_id" => 0,
        ];
    }

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
    protected function questionDefinition()
    {
        return "<p>What is the color of grass [green|red|blue]. What is correct [test|car|fly]-correct</p>";
    }
}