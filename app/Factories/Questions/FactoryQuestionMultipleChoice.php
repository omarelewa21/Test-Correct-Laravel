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
            'score'              => $this->calculateTotalScore(),
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
        return array_merge(
            parent::definition(),
            [
                "selectable_answers" => 0,
                "answers"            => $this->answerDefinition(),
            ]
        );
    }

    public function questionSubType(): string
    {
        return "MultipleChoice";
    }

    public function questionDefinition()
    {
        return "<p>welk getal is groter dan 1?</p>\n";
    }

    public function answerDefinition()
    {
        return [
            0 => [
                "order"  => 1,
                "answer" => "een (1)",
                "score"  => 0,
            ],
            1 => [
                "order"  => 2,
                "answer" => "twee (2)",
                "score"  => 2,
            ],
            2 => [
                "order"  => 3,
                "answer" => "drie (3)",
                "score"  => 3,
            ],
        ];

    }

    public function questionType(): string
    {
        return 'MultipleChoiceQuestion';
    }
}