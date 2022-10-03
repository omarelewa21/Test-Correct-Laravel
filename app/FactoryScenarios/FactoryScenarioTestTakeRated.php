<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeRated extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = "TestTake 'Rated' with all question types";

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken()
            ->setStatusDiscussing()
            ->addTeacherAnswerRatings()
            ->setNormalizedScores()
            ->setStatusRated();
    }
}