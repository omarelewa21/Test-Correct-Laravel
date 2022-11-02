<?php

namespace tcCore\Http\Traits;

use Ramsey\Uuid\Uuid;
use tcCore\Test;

trait WithTestAwarenessProperties
{
    public $addedQuestionIds = [];

    private $testRelations = [
        'testQuestions:id,question_id,test_id,order',
        'testQuestions.question:id,type,derived_question_id'
    ];

    protected function getQuestionIdsThatAreAlreadyInTest($testParameter = null)
    {
        $test = $this->getTestModelByParameter($testParameter);

        $questionIdList = $test->getQuestionOrderList() ?? [];

        return $questionIdList + $test->testQuestions->map(function ($testQ) {
                return $testQ->question()->where('type', 'GroupQuestion')->value('id');
            })->filter()->flip()->toArray();
    }

    public function testContainsQuestion($question)
    {
        return isset($this->addedQuestionIds[$question->id]) || isset($this->addedQuestionIds[$question->derived_question_id]);
    }

    /**
     * @throws \Exception
     */
    private function getTestModelByParameter($testParameter = null)
    {
        if (property_exists($this, 'test') && $this->test instanceof Test) {
            $this->test->loadMissing($this->testRelations);
            return $this->test;
        }

        if (!$testParameter) {
            throw new \Exception('No test provided to check if question is present.');
        }

        if (Uuid::isValid($testParameter)) {
            return Test::whereUuid($testParameter)
                ->with($this->testRelations)
                ->first();
        }

        if ($testParameter instanceof Test) {
            $testParameter->loadMissing($this->testRelations);
            return $testParameter;
        }
    }
}