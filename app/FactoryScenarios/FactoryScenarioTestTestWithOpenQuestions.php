<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Questions\FactoryQuestionCompletionCompletion;
use tcCore\Factories\Questions\FactoryQuestionCompletionMulti;
use tcCore\Factories\Questions\FactoryQuestionGroup;
use tcCore\Factories\Questions\FactoryQuestionInfoscreen;
use tcCore\Factories\Questions\FactoryQuestionMatchingClassify;
use tcCore\Factories\Questions\FactoryQuestionMatchingMatching;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoice;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceARQ;
use tcCore\Factories\Questions\FactoryQuestionMultipleChoiceTrueFalse;
use tcCore\Factories\Questions\FactoryQuestionOpenLong;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Factories\Questions\FactoryQuestionRanking;

/**
 * Scenario details:
 * Create Test with one of every Question type.
 * With default values for the Test and TestQuestions.
 */
class FactoryScenarioTestTestWithOpenQuestions extends FactoryScenarioTest
{
    protected function createFactoryTest(): FactoryTest
    {
        return FactoryTest::create($this->user)
            ->setProperties([
                'name' => $this->testName ?? 'Test with open questions . ' . Carbon::now()->format('ymd-Hi'),
            ])->addQuestions([
                FactoryQuestionOpenShort::create()
                    ->addImageAttachment()
                    ->addAudioAttachment()
                    ->addAudioAttachment(true, true, 250)
                    ->addPdfAttachment(),
                FactoryQuestionOpenLong::create(),
                FactoryQuestionCompletionCompletion::create(),
                FactoryQuestionGroup::create()
                    ->addQuestions([
                        FactoryQuestionOpenLong::create()
                            ->setProperties(['question' => '<p>I am part of a group!</p>']),
                    ]),
            ]);
    }
}