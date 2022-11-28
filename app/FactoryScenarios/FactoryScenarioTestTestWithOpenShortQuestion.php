<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;

/**
 * Scenario details:
 * Create Test with one Open Short Question
 * With default values for the Test and TestQuestion.
 */
class FactoryScenarioTestTestWithOpenShortQuestion extends FactoryScenarioTest
{
    protected function createFactoryTest() : FactoryTest
    {
        return FactoryTest::create($this->user)
            ->setProperties([
                'name' => $this->testName ?? 'Test with openShort question. '.Carbon::now()->format('ymd-Hi')
            ])
            ->addQuestions([
            FactoryQuestionOpenShort::create(),
        ]);
    }
}