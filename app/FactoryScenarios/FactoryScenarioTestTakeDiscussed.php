<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeDiscussed extends FactoryScenarioTestTake
{
    const DEFAULT_TEST_NAME = "TestTake 'Discussed' with all question types";

    protected function createFactoryTestTake()
    {
        return FactoryTestTake::create($this->test, $this->user)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest()
            ->fillTestParticipantsAnswers()
            ->setStatusTaken()
            ->setStatusDiscussing()
            ->addStudentAnswerRatings()
            ->setStatusDiscussed();
    }
}