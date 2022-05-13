<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakingTest extends FactoryScenarioTestTake
{

    protected function createFactoryTestTake()
    {
        $testName = "TestTake 'Taking Test' with all question types";

        $this->test = FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $this->user);

        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest();
    }
}