<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeRated extends FactoryScenarioTestTake
{

    protected function createFactoryTestTake()
    {
        $testName = "TestTake 'Rated' with all question types";

        $this->test = FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $this->user);

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