<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionGroup extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    private $subQuestions;

    /**
     * @param array $answers
     * @return $this
     */
    public function addAnswer(array $answer)
    {
        throw new \Exception("Cannot add answers to group question.");
    }

    protected function definition()
    {
        return array_merge(
            parent::definition(),
            [
                'name'                   => 'Vraaggroep titel',
                'shuffle'                => 0,
                'groupquestion_type'     => 'standard',
                'number_of_subquestions' => 0,
            ]
        );
    }

    public function questionDefinition()
    {
        return "<p>Dit is de beschrijving van de vraaggroep</p>";
    }

    public function answerDefinition()
    {
        return '';
    }

    public function questionType(): string
    {
        return 'GroupQuestion';
    }

    public function questionSubType(): string
    {
        return '';
    }

    public function addQuestions(array $subQuestions)
    {
        $this->subQuestions = collect($subQuestions);

        $this->subQuestions->each(function ($subQuestion) {
            $subQuestion->questionProperties = array_merge(
                $subQuestion->questionProperties,
                [
                    'is_subquestion' => true,
                ]
            );
        });

        return $this;
    }
}