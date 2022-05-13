<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakenTwoQuestions extends FactoryScenarioTestTake
{

    protected function createFactoryTestTake()
    {
        $testName = 'TestTake Taken with two questions';

        $this->test = FactoryScenarioTestTestWithTwoQuestions::createTest($testName, $this->user);

        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken();
    }
}