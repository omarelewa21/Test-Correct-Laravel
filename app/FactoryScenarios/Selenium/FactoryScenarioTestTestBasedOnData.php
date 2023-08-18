<?php

namespace tcCore\FactoryScenarios\Selenium;

use Carbon\Carbon;
use tcCore\Factories\FactoryTest;
use tcCore\Factories\Interfaces\FactoryQuestion;
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

    public static function create(
        string $testName = null,
        User   $user = null,
        string $testDataJson = null
    ): FactoryScenarioTest {
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
            self::validateQuestionRequest($question['type']);
            $questionFactory = self::retrieveQuestionFactory($question);
            return self::forwardProperties($questionFactory, $question);
        })->toArray();
    }

    private static function questionLookup(): array
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

    public function getTestModel()
    {
        $test = $this->testFactory->getTestModel();
        $test->loadMissing(['testQuestions', 'testQuestions.question']);
        $test->questions = $test->testQuestions
            ->map(function ($testQuestion) {
                $question = $testQuestion->question;
                unset($testQuestion->question);
                return $question;
            });
        return $test;
    }

    private static function validateQuestionRequest($type): void
    {
        if (!isset(self::questionLookup()[$type])) {
            throw new \Exception('Unsupported question type added to test factory');
        }
    }

    private static function retrieveQuestionFactory($question): FactoryQuestion
    {
        return self::questionLookup()[$question['type']];
    }

    private static function forwardProperties(FactoryQuestion $questionFactory, $question): FactoryQuestion
    {
        $properties = $questionFactory->definition();
        if (isset($question['settings'])) {
            $properties = array_merge(
                $properties,
                self::getSnakeCaseKeyedArray($question['settings'])
            );
        }
        foreach ($question as $key => $value) {
            if (array_key_exists($key, $properties)) {
                if (!is_array($value)) {
                    $properties[$key] = $value;
                }
            }
        }

        unset($properties['type']);
        return $questionFactory->setProperties($properties);
    }

    private static function getSnakeCaseKeyedArray($settings): array
    {
        return collect($settings)->mapWithKeys(function ($value, $key) {
            return [str($key)->snake()->value => $value];
        })->toArray();
    }
}