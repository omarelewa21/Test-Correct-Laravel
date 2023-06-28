<?php

namespace tcCore\FactoryScenarios\Selenium;

use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\User;

class FactoryScenarioSeleniumAssessment extends FactoryScenarioSchoolCoLearning
{
    protected static string $prefix = 'Assessment';

    protected function createTestTake(User $teacherUser): void
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user    : $teacherUser,
            testName: 'Assessment test for selenium'
        );
        $this->testTake = $testTake;
        $this->test = $testTake->test;
        $this->testTake->subject_name = $this->test->subject()->value('name');
    }
}
