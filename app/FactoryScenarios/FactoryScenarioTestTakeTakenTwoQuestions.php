<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakenTwoQuestions extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = 'TestTake Taken with two questions';

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken();
    }

    protected function createTest()
    {
        return FactoryScenarioTestTestWithTwoQuestions::createTest($this->testName, $this->user);
    }
}