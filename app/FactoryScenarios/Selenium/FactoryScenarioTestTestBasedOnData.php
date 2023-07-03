<?php

namespace tcCore\FactoryScenarios\Selenium;

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
use tcCore\Factories\Questions\FactoryQuestionOpenWriting;
use tcCore\Factories\Questions\FactoryQuestionRanking;
use tcCore\Factories\Questions\FactoryQuestionWriteDown;
use tcCore\FactoryScenarios\FactoryScenarioTest;
use tcCore\User;

/**
 * Scenario details:
 * Create Test with one of every Question type.
 * With default values for the Test and TestQuestions.
 */
class FactoryScenarioTestTestBasedOnData extends FactoryScenarioTest
{
    protected static array $testData = [];

    public static function create(string $testName = null, User $user = null, string $testDataJson = null): FactoryScenarioTest
    {
        self::$testData = json_decode($testDataJson, true);
        return parent::create($testName, $user);
    }

    protected function createFactoryTest(): FactoryTest
    {
        return FactoryTest::create($this->user)
            ->setProperties([
                'name' => $this->testName ?? 'Test with all question types. ' . Carbon::now()->format('ymd-Hi'),
            ])->addQuestions(self::buildQuestionsToAdd());
    }

    private static function buildQuestionsToAdd(): array
    {
        if (!isset(self::$testData['questions'])) {
            throw new \Exception('No question data specified for the test factory');
        }

        return collect(self::$testData['questions'])->map(function ($question) {
            if (!isset(self::questionLookup()[$question['type']])) {
                throw new \Exception('Unsupported question type added to test factory');
            }

            $class = self::questionLookup()[$question['type']];
            if (isset($question['settings'])) {
                $class->setProperties($question['settings']);
            }
            return $class;
        })->toArray();
    }

    private static function questionLookup()
    {
        return [
            'WriteDown'               => FactoryQuestionWriteDown::create(),
            'CompletionCompletion'    => FactoryQuestionCompletionCompletion::create(),
            'CompletionMulti'         => FactoryQuestionCompletionMulti::create(),
            'Group'                   => FactoryQuestionGroup::create(),
            'Infoscreen'              => FactoryQuestionInfoscreen::create(),
            'MatchingClassify'        => FactoryQuestionMatchingClassify::create(),
            'MatchingMatching'        => FactoryQuestionMatchingMatching::create(),
            'MultipleChoiceARQ'       => FactoryQuestionMultipleChoiceARQ::create(),
            'MultipleChoiceTrueFalse' => FactoryQuestionMultipleChoiceTrueFalse::create(),
            'MultipleChoice'          => FactoryQuestionMultipleChoice::create(),
            'Ranking'                 => FactoryQuestionRanking::create(),
        ];
    }
}