<?php

namespace tcCore\Factories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Factories\Answers\FactoryAnswerCompletionQuestion;
use tcCore\Factories\Answers\FactoryAnswerInfoscreenQuestion;
use tcCore\Factories\Answers\FactoryAnswerMatchingQuestion;
use tcCore\Factories\Answers\FactoryAnswerMultipleChoiceQuestion;
use tcCore\Factories\Answers\FactoryAnswerOpenQuestion;
use tcCore\Factories\Answers\FactoryAnswerRankingQuestion;
use tcCore\Factories\Traits\DieAndDumpAble;
use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;
use tcCore\InfoscreenQuestion;
use tcCore\Lib\TestParticipant\Factory;
use tcCore\Period;
use tcCore\Student;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;

class FactoryTestTake
{
    use PropertyGetableByName;
    use DoWhileLoggedInTrait;
    use DieAndDumpAble;

    public array $testTakeProperties = [];
    public TestTake $testTake;
    public Test $test;
    protected ?User $user;

    public static function create(Test $test, User $user = null): FactoryTestTake
    {
        $factory = new static;

        $factory->test = $test;

        //create testtake without participants:
        $factory->testTake = new TestTake();
        $factory->testTake->fill($factory->testTakeDefinition());
        $factory->testTake->setAttribute('user_id', $factory->test->author_id);

        if (!$factory->doWhileLoggedIn(function () use ($factory) {
            return $factory->testTake->save();
        }, User::find($factory->test->author_id))) {
            throw new \Exception('Unable to save TestTake');
        }
        $factory->test = $factory->testTake->fresh()->test;

        return $factory;
    }

    public static function createWithParticipants(Test $test, array $testParticipantFactories = null)
    {
        return self::create($test)->addParticipants($testParticipantFactories);
    }

    public function store()
    {
        $this->testTake->fill($this->testTakeProperties);

        $this->handleUnfillableProperties();

        if (!$this->doWhileLoggedIn(function () {
            return $this->testTake->save();
        }, User::find($this->test->author_id))) {
            throw new \Exception('Unable to save TestTake');
        }
        return $this;
    }

    public function setProperties(array $properties, $saveTestTake = true): FactoryTestTake
    {
        $this->testTakeProperties = array_merge($this->testTakeProperties, $properties);

        if ($saveTestTake) {
            $this->store();
        }

        return $this;
    }

    /**
     * Add a class or a number of students of one class to the TestTake
     * @param array $participantFactories
     * @return void
     */
    public function addParticipants(array $testParticipantFactories = null)
    {
        if (!$testParticipantFactories) {
            return $this->addFirstSchoolClassAsParticipants();
        }

        foreach ($testParticipantFactories as $factoryTestParticipant) {
            $testParticipantModels = $factoryTestParticipant->storeParticipants($this->testTake);
            if (!$this->testTake->testParticipants()->saveMany($testParticipantModels)) {
                throw new \Exception('TestParticipant(s) could not be added');
            }
        }
        $this->testTake->refresh();

        return $this;
    }

    public function addRandomParticipants()
    {
        $testParticipantFactory = FactoryTestParticipant::createValidClassWithTestTake($this->testTake);

        $this->addParticipants([$testParticipantFactory]);

        return $this;
    }

    public function addFirstSchoolClassAsParticipants() //addParticipants... from first school class
    {
        $testParticipantFactory = FactoryTestParticipant::createValidClassWithTestTake($this->testTake, true);

        $this->addParticipants([$testParticipantFactory]);

        return $this;
    }

    public function setTestParticipantsTakingTest()
    {
        $this->testTake->testParticipants->each(function ($testParticipant) {
            $this->setTestParticipantTakingTest($testParticipant);
        });

        return $this;
    }

    public function fillTestParticipantsAnswers()
    {
        $this->testTake->testParticipants->each(function ($testParticipant, $key) {
            $this->fillAllAnswersForParticipant($testParticipant);
        });

        return $this;
    }

    public function addStudentAnswerRatings()
    {
        //create (max)2 AnswerRatings for each questionAnswer by other students
        //Answers get shuffled and the participants need to give a rating for each answer,
        // for 2 other students per question
        $answersPerTestParticipant = $this->testTake->testParticipants->count();
        if ($answersPerTestParticipant > 2) {
            $answersPerTestParticipant = 2;
        }
        // this means 2 answerRatings per testPartipant per Question
        //      each TestParticipant needs to rate (1 or) 2 answers per question

        $testQuestions = $this->testTake->test->testQuestions;

        $testAnswers = [];
        foreach ($testQuestions as $testQuestion) {
            $questionId = $testQuestion->question_id;

            foreach ($this->testTake->testParticipants as $testParticipant) {
                foreach ($testParticipant->answers as $answer) {
                    if ($answer->question_id == $questionId) {
                        $testAnswers[$questionId][$testParticipant->user_id] = $answer->id;
                    }
                }
            }
        }

        foreach ($testAnswers as $testAnswer) {
            for ($i = 0; $i < $answersPerTestParticipant; $i++) {

                $values = array_values($testAnswer);
                array_push($values, array_shift($values));
                $testAnswer = array_combine(array_keys($testAnswer), $values);

                //$testAnswer is now shuffled
                foreach ($testAnswer as $testParticipantId => $answerId) {
                    if (Answer::find($answerId)->getAttribute('json') === null) {
                        continue;
                    }

                    $answerRating = new AnswerRating();
                    $answerRating->setAttribute('answer_id', $answerId);
                    $answerRating->setAttribute('user_id', $testParticipantId);
                    $answerRating->setAttribute('test_take_id', $this->testTake->getKey());
                    $answerRating->setAttribute('type', 'STUDENT');
                    $answerRating->setAttribute('rating', rand(1, 5));

                    $answerRating->save();
                }
            }
        }
        return $this;
    }

    public function addTeacherAnswerRatings()
    {
        $this->testTake->testParticipants->each(function ($testParticipant) {
            $testParticipant->answers->each(function ($answer) {
                if($answer->question instanceof InfoscreenQuestion) {
                    return;
                }

                $answerRating = new AnswerRating([
                    'type'         => 'TEACHER',
                    'answer_id'    => $answer->getKey(),
                    'user_id'      => $this->testTake->user_id,//teacher id....
                    'test_take_id' => $this->testTake->getKey(),
                    'rating'       => (string)rand(1, 5),
                ]);
                $answerRating->save();
            });
        });
        return $this;
    }

    public function setStatus($statusId)
    {
        $this->testTakeProperties['test_take_status_id'] = $statusId;

        $this->store();

        return $this;
    }

    public function setStatusPlanned()
    {
        return $this->setStatus(1);
    }

    public function setStatusTakingTest()
    {
        return $this->setStatus(3);
    }

    public function setStatusTaken()
    {
        return $this->setStatus(6);
    }

    public function setStatusDiscussing(bool $openQuestionsOnly = true)
    {
        $this->setProperties([
            'discussion_type' => ($openQuestionsOnly ? 'OPEN_ONLY' : 'ALL'),
            'discussing_question_id' => $this->test->testQuestions->sortBy('order')->first()->question_id,
        ]);
        $this->setStatus(7);
        return $this;
    }

    public function setStatusDiscussed()
    {
        return $this->setStatus(8);
    }

    public function setStatusRated()
    {
        return $this->setStatus(9);
    }

    protected function testTakeDefinition()
    {
        return [
            'period_id'               => FactoryPeriod::getFirstPeriodForUser($this->test->author)->id, //todo use PeriodFactory? period based on user id?
            'weight'                  => '5',
            'allow_inbrowser_testing' => '1', //default allow inbrowser testing
            'guest_accounts'          => '0',
            'invigilator_note'        => '',
            'time_start'              => Carbon::today(),
            'retake'                  => 0,
            'test_take_status_id'     => 1,
            'exported_to_rtti'        => NULL,

            'invigilators'   =>
                [
                    (string)$this->test->author_id, //by default the teacher that is logged in when creating TestTake
                ],
            //TEST
            'test_id'        => $this->test->getKey(),
            //CLASS
            'school_classes' => [],

        ];
    }

    protected function maxScore()
    {
        return $this->test->testQuestions->reduce(function ($carry, $question) {
            return $carry + $question->question->score;
        }, 0);
    }

    /**
     * Set normalized scores for the testParticipants.
     * ONLY works with n-term scores (default) at the moment.
     */
    public function setNormalizedScores(string $normalizeType = 'n_term') //wrong name used for action it performs?
    {
        switch ($normalizeType){
            case 'n_term':
                $this->nTerm = 1.0;
                break;
            default:
                throw new \Exception('This score normalisation type has not (yet) been implemented');
                break;
        }
        $this->questionsTotalScore = $this->maxScore();

        $this->testTake->testParticipants->each(function ($testParticipant){
            $score = 0;
            $testParticipant->answers->each(function ($answer) use (&$score) {
                if($answer->question instanceof InfoscreenQuestion) {
                    return;
                }

                $answerScore = $answer->calculateFinalRating();
                if ($answerScore) {
                    $answer->setAttribute('final_rating', $answerScore);
                    $answer->save();
                }
                $score += $answerScore;
            });

            // nTerm specific code:
            $testParticipant->setAttribute('rating', $this->normalizeNTermScore($score));

            $testParticipant->save();
        });

        return $this;
    }

    public function normalizeScoreRequestExamples($preview=1): object
    {
        return (object) [
            'pppExample' => [
                "ignore_questions" => [],
                "preview"          => $preview,
                "ppp"              => "1", //amount correct needed per point, 1.5 is 15 'score' needed for a 10
            ],
            'eppExample' => [
                "ignore_questions" => [],
                "preview"          => $preview,
                "epp"              => "1",
            ],
            'WanAvgExample' => [
                "ignore_questions" => [],
                "preview"          => $preview,
                "wanted_average"   => "7.5",
            ],
            'n_termExample' => [
                "ignore_questions" => [],
                "preview"          => $preview,
                "n_term"           => "1",
            ],
            'CesuurExample' => [
                "ignore_questions" => [],
                "preview"          => $preview,
                "n_term"           => "1",
                "pass_mark"        => "50",
            ]
        ];
    }

    public function normalizeNTermScore(float $score)
    {
        $rate = (9.0 * ($score / $this->questionsTotalScore)) + $this->nTerm;

        if ($this->nTerm > 1) {
            $scoreMin = 1.0 + ($score * (9 / $this->questionsTotalScore) * 2);
            $scoreMax = 10 - (($this->questionsTotalScore - $score) * (9 / $this->questionsTotalScore) * 0.5);

            if ($scoreMin < $scoreMax && $rate > $scoreMin) {
                $rate = $scoreMin;
            } elseif ($scoreMin > $scoreMax && $rate > $scoreMax) {
                $rate = $scoreMax;
            }

        } elseif ($this->nTerm < 1) {
            $scoreMin = 1.0 + ($score * (9 / $this->questionsTotalScore) * 0.5);
            $scoreMax = 10 - (($this->questionsTotalScore - $score) * (9 / $this->questionsTotalScore) * 2);

            if ($scoreMin > $scoreMax && $rate < $scoreMin) {
                $rate = $scoreMin;
            } elseif ($scoreMin < $scoreMax && $rate < $scoreMax) {
                $rate = $scoreMax;
            }
        } else {
            $scoreMin = 1;
            $scoreMax = 10;

            if ($rate < $scoreMin) {
                $rate = $scoreMin;
            } elseif ($rate > $scoreMax) {
                $rate = $scoreMax;
            }
        }

        return round($rate, 1);
    }

    /**
     * @param $testParticipant
     * @return void
     * @throws \Exception
     */
    function fillAllAnswersForParticipant($testParticipant): void
    {
        $testParticipant->answers->each(function ($answer) {
            $this->fillAnswer($answer);
        });
    }

    /**
     * @param $answer
     * @return void
     * @throws \Exception
     */
    public function  fillAnswer($answer)
    {
        $lookUp = [
            'InfoscreenQuestion'     => FactoryAnswerInfoscreenQuestion::class,
            'OpenQuestion'           => FactoryAnswerOpenQuestion::class,
            'CompletionQuestion'     => FactoryAnswerCompletionQuestion::class,
            'RankingQuestion'        => FactoryAnswerRankingQuestion::class,
            'MultipleChoiceQuestion' => FactoryAnswerMultipleChoiceQuestion::class,
            'MatchingQuestion'       => FactoryAnswerMatchingQuestion::class,
        ];

        if (!array_key_exists($answer->question->type, $lookUp) ) {
            throw new \Exception($answer->question->type . ' is not implemented (yet).');
        }

        $factory = $lookUp[$answer->question->type];
        $factory::generate($answer);
    }

    /**
     * @param $testParticipant
     * @return void
     */
    function setTestParticipantTakingTest($testParticipant): void
    {
        $testParticipant->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
        $testParticipant->save();
    }

    /**
     * @return void
     */
    private function handleUnfillableProperties(): void
    {
        if (isset($this->testTakeProperties['user_id'])) {
            $this->testTake->setAttribute('user_id', $this->testTakeProperties['user_id']);
        }
        if (isset($this->testTakeProperties['discussing_question_id'])) {
            $this->testTake->setAttribute('discussing_question_id', $this->testTakeProperties['discussing_question_id']);
        }
    }
}
