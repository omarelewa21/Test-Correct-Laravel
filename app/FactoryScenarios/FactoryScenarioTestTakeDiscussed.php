<?php

namespace tcCore\FactoryScenarios;

use tcCore\Factories\FactoryTestTake;

class FactoryScenarioTestTakeDiscussed extends FactoryScenarioTestTake
{

    protected function createFactoryTestTake()
    {
        $testName = "TestTake 'Discussed' with all question types";

        $this->test = FactoryScenarioTestTestWithAllQuestionTypes::createTest($testName, $this->user);

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