<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakenOneQuestion extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = 'TestTake Taken with open-short question';

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
        return FactoryScenarioTestTestWithOpenShortQuestion::createTest($this->testName, $this->user);
    }
}