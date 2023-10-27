<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeAssessed extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = "TestTake 'Rated' with all question types";

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)
            ->setProperties(['draft' => 0])
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken()
            ->setStatusDiscussing()
            ->addTeacherAnswerRatings();
    }
}