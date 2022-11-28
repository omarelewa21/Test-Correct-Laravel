<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;
use function collect;

class FactoryQuestionMultipleChoice extends FactoryQuestion
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
            throw new \Exception("Adding an multipleChoiceAnswers data array to the method addAnswers() is required\n[\n\t0 => [\n\t\t'order' => 1,\n\t\t'answer' => 'answer_text',\n\t\t'score' => 2, \n\t],\n]");
        }

        $this->questionProperties['answers'] = $answers;

        return $this;
    }


    protected function calculatedQuestionProperties(): array
    {
        return [
            'score' => $this->calculateTotalScore(),
            'selectable_answers' => $this->calculateSelectableAnswers(),
        ];
    }

    private function calculateSelectableAnswers()
    {
        return collect($this->questionProperties['answers'])->where('score', '>', 0)->count();
    }

    protected function calculateTotalScore()
    {
        return collect($this->questionProperties['answers'])->sum('score');
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
            "subtype" => $this->questionSubType(),
            "type" => "MultipleChoiceQuestion",
            "attainments" => [],
            "all_or_nothing" => false,
            //The following can be overridden in child classes
            "question" => $this->questionDefinition(),
            "answers" => $this->answerDefinition(),
            //The following need to be calculated/set before saving
            "test_id" => 0,
            "score" => 0,
            "selectable_answers" => 0,
        ];
    }

    protected function questionSubType()
    {
        return "MultipleChoice";
    }

    protected function questionDefinition()
    {
        return "<p>welk getal is groter dan 1?</p>\n";
    }

    protected function answerDefinition()
    {
        return [
            0 => [
                "order" => 1,
                "answer" => "een (1)",
                "score" => 0,
            ],
            1 => [
                "order" => 2,
                "answer" => "twee (2)",
                "score" => 2,
            ],
            2 => [
                "order" => 3,
                "answer" => "drie (3)",
                "score" => 3,
            ],
        ];

    }
}