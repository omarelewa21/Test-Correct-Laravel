<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakePlanned extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = "TestTake 'Planned' with all question types";

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)->addFirstSchoolClassAsParticipants();
    }
}