<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakenOneQuestion extends FactoryScenarioTestTake
{
    protected function createFactoryTestTake()
    {
        $testName = 'TestTake Taken with open-short question';

        $this->test = FactoryScenarioTestTestWithOpenShortQuestion::createTest($testName, $this->user);

        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken();
    }
}