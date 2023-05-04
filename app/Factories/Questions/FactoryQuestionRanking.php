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

    protected function definition()
    {
        return array_merge(
            parent::definition(),
            [
                "answers" => $this->answerDefinition(),
            ]);
    }

    public function questionDefinition()
    {
        return "<p>sorteer van laag naar hoog</p>";
    }

    public function answerDefinition()
    {
        return [
            0 => [
                "order"  => 1,
                "answer" => "een (1)",
            ],
            1 => [
                "order"  => 2,
                "answer" => "twee (2)",
            ],
            2 => [
                "order"  => 3,
                "answer" => "drie (3)",
            ],
        ];

    }

    public function questionType(): string
    {
        return 'RankingQuestion';
    }

    public function questionSubType(): string
    {
        return 'ranking';
    }
}