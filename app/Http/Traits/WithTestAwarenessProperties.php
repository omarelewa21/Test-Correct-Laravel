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

    public function setAddedQuestionIdsArray($test = null): void
    {
        $this->addedQuestionIds = $this->getQuestionIdsThatAreAlreadyInTest($test);
    }

    protected function removeQuestionFromTest($questionId)
    {
        $this->addedQuestionIds = collect($this->addedQuestionIds)->reject(function ($index, $id) use ($questionId) {
            return $id == $questionId;
        });
    }

    protected function getQuestionIdsThatAreAlreadyInTest($testParameter = null)
    {
        $test = $this->getTestModel($testParameter);

        $questionIdList = $test->getQuestionOrderList() ?? [];

        return $questionIdList + $test->testQuestions->map(function ($testQ) {
                return $testQ->question()->where('type', 'GroupQuestion')->value('id');
            })->filter()->flip()->toArray();
    }

    public function testContainsQuestion($question)
    {
        return isset($this->addedQuestionIds[$question->id]) || isset($this->addedQuestionIds[$question->derived_question_id]);
    }


    private function getTestModel($testParameter = null)
    {
        if ($testParameter) {
            return $this->getTestModelByParameter($testParameter);
        }

        if (property_exists($this, 'test')) {
            if ($this->test instanceof Test) {
                $this->test->loadMissing($this->testRelations);
                return $this->test;
            }
        }

        throw new \Exception('No test provided to check if question is present.');
    }

    /**
     * @throws \Exception
     */
    private function getTestModelByParameter($testParameter)
    {
        if ($testParameter instanceof Test) {
            $testParameter->loadMissing($this->testRelations);
            return $testParameter;
        }

        if (Uuid::isValid($testParameter)) {
            return Test::whereUuid($testParameter)
                ->with($this->testRelations)
                ->first();
        }

        throw new \Exception('Cannot resolve Test with given parameter.');
    }
}