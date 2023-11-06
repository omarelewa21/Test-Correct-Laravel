<?php

namespace tcCore\Factories;

use tcCore\EducationLevel;
use tcCore\Factories\Questions\FactoryQuestionCompletionCompletion;
use tcCore\Factories\Questions\FactoryQuestionCompletionMulti;
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
use tcCore\Factories\Traits\PropertyGetableByName;
use tcCore\Period;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;

const DEFAULT_TEACHER = 1486;
const DEFAULT_STUDENT = 1483;

const PRACTICE_TEST_KIND = 1;
const FORMATIVE_TEST_KIND = 2;
const SUMMATIVE_TEST_KIND = 3;
const ASSIGNMENT_TEST_KIND = 4;

class FactoryTest
{
    use PropertyGetableByName;

    private Test $test;
    private Subject $testSubject;
    private Period $testPeriod;
    private EducationLevel $testEducationLevel;
    private ?User $user;

    /**
     * Create a new test record
     * @return false|FactoryTest
     */
    public static function create(User $user = null, array $properties = []): FactoryTest
    {
        $testFactory = new self;
        if (!$testFactory->user = $user) {
            $testFactory->user = User::find(DEFAULT_TEACHER);
        }

        $testProperties = array_merge($testFactory->testDefinition(), $properties);

        $testFactory->test = new Test($testProperties);
        $testFactory->test->setAttribute('author_id', $testProperties['author_id']);
        $testFactory->test->setAttribute('owner_id', $testProperties['owner_id']);
        $testFactory->test->setAttribute('is_system_test', $testProperties['is_system_test']);

        if (!$testFactory->test->save()) {
            return false;
        }
        if (in_array($testProperties['draft'], [0, false])) {
            $testFactory->test->setAttribute('draft', false);
            $testFactory->test->save();
        }
        return $testFactory;
    }

    /**
     * @param array $properties
     * @return $this FactoryTest
     */
    public function setProperties(array $properties): FactoryTest
    {
        foreach ($properties as $property => $value) {
            $this->test->setAttribute($property, $value);
        }
        $this->test->save();
        return $this;
    }

    public function addQuestions(array $questions): FactoryTest
    {
        $this->questions = collect($questions)->each(function ($question) {
            $question->setTestModel($this->test);
            $question->store();
            $question->handleAttachments();

            if($question->questionType() === 'GroupQuestion' && $question->subQuestions) {
                $question->subQuestions->each(function($subQuestion) {
                    $subQuestion->setTestModel($this->test);
                    $subQuestion->store();
                    $subQuestion->handleAttachments();
                });
            }
        });



        $this->test->refresh();

        return $this;
    }

    public function addAllQuestions()
    {
        $this->addQuestions([
            FactoryQuestionInfoscreen::create(),
            FactoryQuestionRanking::create(),
            FactoryQuestionOpenShort::create(),
            FactoryQuestionOpenLong::create(),
            FactoryQuestionMultipleChoiceTrueFalse::create(),
            FactoryQuestionMultipleChoice::create(),
            FactoryQuestionMultipleChoiceARQ::create(),
            FactoryQuestionCompletionCompletion::create(),
            FactoryQuestionCompletionMulti::create(),
            FactoryQuestionMatchingMatching::create(),
            FactoryQuestionMatchingClassify::create(),
            FactoryQuestionOpenWriting::create(),
        ]);

        return $this;
    }

    public function addRandomQuestions(int $amount = 1): FactoryTest
    {
        $questions = [];

        $availableQuestions = [
            FactoryQuestionMultipleChoice::class,
            FactoryQuestionOpenShort::class,
            FactoryQuestionOpenLong::class,
        ];

        for ($i = 0; $i < $amount; $i++) {
            $questions[] = $availableQuestions[rand(0, count($availableQuestions) - 1)]::create();
        }

        $this->addQuestions($questions);

        return $this;
    }


    public function getTestId(): int
    {
        if (!isset($this->test)) {
            return false;
        }
        return $this->test->id;
    }

    public function getTestModel(): Test
    {
        return $this->test;
    }

    public function getPeriodModel(): Period
    {
        return $this->testPeriod;
    }

    public function getSubjectModel(): Subject
    {
        return $this->testSubject;
    }

    /**
     * returns a default test example
     * @param $userId
     * @return array
     */
    protected function testDefinition(): array
    {
        $this->schoolLocationID = $this->user->school_location_id;

        $this->testPeriod = FactoryPeriod::getFirstPeriodForUser($this->user);
        $this->testSubject = FactorySubject::getFirstSubjectForUser($this->user);
        $this->testEducationLevel = FactoryEducationLevel::getFirstEducationLevelForUser($this->user);

        return [
            "name"                 => 'test-' . rand(1000,9999) . rand(1000,9999),
            "abbreviation"         => 'TEST',
            "test_kind_id"         => SUMMATIVE_TEST_KIND,
            "subject_id"           => $this->testSubject->id,
            "education_level_id"   => $this->testEducationLevel->id,
            "education_level_year" => "1", //choose from range of 1 to $this->testEducationLevel->max_years
            "period_id"            => $this->testPeriod->id,
            "shuffle"              => "0", //(string) random_int(0,1),
            "introduction"         => "Default test introduction",
            "author_id"            => $this->user->id,  //Auth::id()
            "owner_id"             => $this->schoolLocationID,  //Auth::user()->school_location_id
            "is_system_test"       => 0,
            "draft"                => false,
        ];
    }
}