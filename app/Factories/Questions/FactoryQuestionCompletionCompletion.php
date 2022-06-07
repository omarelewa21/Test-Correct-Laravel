<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionCompletionCompletion extends FactoryQuestion
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
            "subtype" => "completion",
            "type" => "CompletionQuestion",
            "attainments" => [],
            "all_or_nothing" => false,
            "score" => 5,
            //Completion\CompletionQuestion specific
            'auto_check_answer' => false,
            'auto_check_answer_case_sensitive' => false,
            //The following can be overridden in child classes
            "question" => $this->questionDefinition(),
            //The following need to be calculated/set before saving
            "test_id" => 0,
        ];
    }

    /**
     * The question contains the answers:
     *
     * Answers are surrounded by square brackets []
     * Answers are formatted like: [correct answer]
     *
     * @return string
     */
    protected function questionDefinition() : string
    {
        return "<p>What is the color of grass [green]. What is the color of the sky? [blue].</p>";
    }
}