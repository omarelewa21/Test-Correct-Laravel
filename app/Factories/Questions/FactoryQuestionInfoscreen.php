<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionInfoscreen extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    protected function definition()
    {
        //generate default question, with default answers, override answers later if provided.
        return [
            "add_to_database" => 1,
            "bloom" => "",
            "closeable" => false,
            "decimal_score" => 0,
            "discuss" => 0,
            "maintain_position" => false,
            "miller" => "",
            "is_open_source_content" => 0,
            "tags" => [],
            "note_type" => "NONE",
            "order" => 0,
            "rtti" => "",
            "subtype" => "info",
            "type" => "InfoscreenQuestion",
            "attainments" => [],
            "all_or_nothing" => false,
            "score" => 0,
            "answer" => '',
            //The following can be overridden in child classes
            "question" => $this->questionDefinition(),
            //The following need to be calculated/set before saving
            "test_id" => 0,
        ];
    }

    protected function questionDefinition()
    {
        return "<p>Information screen (question)</p>";
    }
}