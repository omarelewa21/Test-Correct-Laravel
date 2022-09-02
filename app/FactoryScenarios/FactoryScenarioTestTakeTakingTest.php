<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeTakingTest extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = "TestTake 'Taking Test' with all question types";

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest();
    }
}