<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTaken extends FactoryScenarioTestTake
{

    protected function createFactoryTestTake()
    {
        $testName = 'TestTake Taken with all question types';

        $this->test = FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $this->user);

        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken();
    }
}