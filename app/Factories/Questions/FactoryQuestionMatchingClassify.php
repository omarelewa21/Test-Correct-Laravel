<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionMatchingClassify extends FactoryQuestion
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
            "subtype" => "Classify",
            "type" => "MatchingQuestion",
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
        return "<p>Classify question</p>";
    }

    /**
     * the "right" answer needs to be separated by newlines,
     * correct:
     * "right" => "text\ntext\ntext",
     * "right" => "text
     * text
     * text",
     * incorrect:
     * 'right' => 'text\ntext\ntext'  //single quotes make \n act like a string, not a linebreak
     * "right" => "text
     *      text
     *      text"
     * The last example returns: "        text" as second/third answer!!
     * @return array[]
     */
    protected function answerDefinition()
    {
        return [
            [
                "order" => 1,
                "left" => "car",
                "right" => "wheels\nwindows\nsteering wheel",
            ],
            [
                "order" => 2,
                "left" => "spaghetti",
                "right" => "meatballs",
            ],
            [
                "order" => 3,
                "left" => "computer",
                "right" => "monitor
keyboard",
            ],
        ];

    }
}