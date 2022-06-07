<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionRanking extends FactoryQuestion
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
            throw new \Exception("Adding an rankingAnswers data array to the method addAnswers() is required");
        }

        $this->questionProperties['answers'] = $answers;

        return $this;
    }

    public function setScore(int $score)
    {
        $this->questionProperties['score'] = $score;

        return $this;
    }

    protected function definition()
    {
        //generate default question, with default answers, override answers later if provided.
        return [
            "add_to_database" => 1,
            "bloom" => "",
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
            "subtype" => "ranking",
            "type" => "RankingQuestion",
            "attainments" => [],
            "all_or_nothing" => false,
            "score" => 5,
            //The following can be overridden in child classes
            "question" => $this->questionDefinition(),
            "answers" => $this->answerDefinition(),
            //The following need to be calculated/set before saving
            "test_id" => 0,
        ];
    }

    protected function questionDefinition()
    {
        return "<p>sorteer van laag naar hoog</p>";
    }

    protected function answerDefinition()
    {
        return [
            0 => [
                "order" => 1,
                "answer" => "een (1)",
            ],
            1 => [
                "order" => 2,
                "answer" => "twee (2)",
            ],
            2 => [
                "order" => 3,
                "answer" => "drie (3)",
            ],
        ];

    }
}