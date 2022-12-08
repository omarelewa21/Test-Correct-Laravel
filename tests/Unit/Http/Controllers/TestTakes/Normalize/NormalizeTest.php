<?php

namespace Tests\Unit\Http\Controllers\TestTakes\Normalize;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\TestTake;
use tcCore\User;
use tests\TestCase;

class NormalizeTest extends TestCase
{
    use DatabaseTransactions;

    protected FactoryTestTake $testTakeFactory;
    protected User $user;
    protected object $normalizeScoreRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->getTeacherOne();
        $this->testTakeFactory = FactoryScenarioTestTakeRated::create($this->user)->testTakeFactory;
        $this->normalizeScoreRequest = $this->testTakeFactory->normalizeScoreRequestExamples();
    }

    protected function connector(array $payload): TestResponse
    {
        $url = "{$this->baseUrl}/api-c/test_take/{$this->testTakeFactory->testTake->uuid}/normalize?user={$this->user->username}";
        return $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson($url, $payload);
    }

    protected function executeTest(array $payload)
    {
        $requestResonse = $this->connector($payload);
        $oldData = $this->oldNormalizeMethod(TestTake::whereUuid($this->testTakeFactory->testTake->uuid)->first(), $payload);
        $oldData->makeHidden('updated_at')->testParticipants->map(
            fn($testParticipant) => $testParticipant->makeHidden('updated_at')
        );
        $requestResonse
            ->assertOk()
            ->assertJson($oldData->toArray());
    }

    public function testNormBasedOnGoodPerPoint()
    {
        $this->executeTest($this->normalizeScoreRequest->pppExample);
    }

    public function testNormBasedOnErrorsPerPoint()
    {
        $this->executeTest($this->normalizeScoreRequest->eppExample);
    }

    public function testNormBasedOnAverageMark()
    {
        $this->executeTest($this->normalizeScoreRequest->WanAvgExample);
    }

    public function testNormBasedOnNTerm()
    {
        $this->executeTest($this->normalizeScoreRequest->n_termExample);
    }
    
    public function testNormBasedOnNTermAndPassMark()
    {
        $this->executeTest($this->normalizeScoreRequest->n_termExample);
    }
    

    /**
     * execute old method and retireve results
     * 
     * @param tcCore/TestTake $testTake
     * @param array $payload
     * 
     * @return tcCore/TestTake
     */
    private function oldNormalizeMethod(TestTake $testTake, array $payload)
    {
        $testTake->load(['testParticipants', 'testParticipants.user', 'testParticipants.answers', 'testParticipants.answers.answerRatings', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                $query->orderBy('level');
            }]);
        $ignoreQuestions = Arr::get($payload, 'ignore_questions');

        if (Arr::has($payload, 'ppp') || Arr::has($payload, 'epp') || Arr::has($payload, 'wanted_average') || Arr::has($payload, 'n_term')) {
            $testTake->setAttribute('ppp', null);
            $testTake->setAttribute('epp', null);
            $testTake->setAttribute('wanted_average', null);
            $testTake->setAttribute('n_term', null);
            $testTake->setAttribute('pass_mark', null);
        }

        $questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);
        if (
            (
                (
                    !Arr::has($payload, 'ppp')
                    && $testTake->getAttribute('ppp') === null
                )
                && (
                    Arr::has($payload, 'epp')
                    || $testTake->getAttribute('epp')
                )
            )
            || (
                (
                    !Arr::has($payload, 'ppp')
                    && $testTake->getAttribute('ppp') === null
                    && !Arr::has($payload, 'epp')
                    && $testTake->getAttribute('epp') === null
                    && !Arr::has($payload, 'wanted_average')
                    && $testTake->getAttribute('wanted_average') === null
                )
                && (
                    Arr::has($payload, 'n_term')
                    || (
                        $testTake->getAttribute('n_term') !== null
                    )
                )
            )
        ) {
            // $totalScore = 0;
            // foreach ($questions as $questionId => $question) {
            //     if ($ignoreQuestions === null || !in_array($questionId, $ignoreQuestions)) {
            //         $totalScore += $question->getAttribute('score');
            //     }
            // }
            $totalScore = $testTake->maxScore($ignoreQuestions);
        } else {
            $totalScore = null;
        }
        $scores = [];
        foreach ($testTake->testParticipants as $testParticipant) {
            $score = 0;

            foreach ($testParticipant->answers as $answer) {
                $answerQuestionId = null;
                foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                    if ($answerQuestionId !== null) {
                        $answerQuestionId .= '.';
                    }
                    $answerQuestionId .= $answerParentQuestion->getAttribute('group_question_id');
                }

                if ($answerQuestionId !== null) {
                    $answerQuestionId .= '.';
                }
                $answerQuestionId .= $answer->getAttribute('question_id');

                if ($ignoreQuestions !== null && in_array($answerQuestionId, $ignoreQuestions)) {
                    $answer->setAttribute('ignore_for_rating', true);
                } else {
                    $answer->setAttribute('ignore_for_rating', false);
                    $answerScore = $answer->getAttribute('final_rating');

                    if ($answerScore === null) {
                        $answerScore = $answer->calculateFinalRating();
                        if ($answerScore !== null) {
                            $answer->setAttribute('final_rating', $answerScore);
                        }
                    }

                    if ($score !== false && $answerScore !== null) {
                        $score += $answerScore;
                    } else {
                        $score = false;
                    }
                }

                $answer->save();
            }

            if (!$testParticipant->answers->isEmpty() && $score !== false) {
                $scores[$testParticipant->getKey()] = $score;
            }

            $relations = $testParticipant->getRelations();
            unset($relations['answers']);
            $testParticipant->setRelations($relations);
        }

        if (Arr::has($payload, 'ppp') || $testTake->getAttribute('ppp') !== null) {
            $ppp = (Arr::has($payload, 'ppp')) ? Arr::get($payload, 'ppp') : $testTake->getAttribute('ppp');
            $testTake->setAttribute('ppp', $ppp);
            if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                $testTake->save();
            }
            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = ($score / $ppp);
                    if ($rate < 1) {
                        $rate = 1;
                    } elseif ($rate > 10) {
                        $rate = 10;
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif (Arr::has($payload, 'epp') || $testTake->getAttribute('epp') !== null) {
            $epp = (Arr::has($payload, 'epp')) ? Arr::get($payload, 'epp') : $testTake->getAttribute('epp');

            $testTake->setAttribute('epp', $epp);
            if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = 10 - (($totalScore - $score) / $epp);
                    if ($rate < 1) {
                        $rate = 1;
                    } elseif ($rate > 10) {
                        $rate = 10;
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif (Arr::has($payload, 'wanted_average') || $testTake->getAttribute('wanted_average') !== null) {
            $average = (Arr::has($payload, 'wanted_average')) ? Arr::get($payload, 'wanted_average') : $testTake->getAttribute('wanted_average');
            $testTake->setAttribute('wanted_average', $average);
            if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                $testTake->save();
            }
            if ($scores) {
                $ppp = ((array_sum($scores) / count($scores)) / ($average - 1));
                foreach ($testTake->testParticipants as $testParticipant) {
                    if (array_key_exists($testParticipant->getKey(), $scores)) {
                        $score = $scores[$testParticipant->getKey()];
                        $rate = 1 + ($score / $ppp);
                        if ($rate < 1) {
                            $rate = 1;
                        } elseif ($rate > 10) {
                            $rate = 10;
                        }

                        $testParticipant->setAttribute('rating', round($rate, 1));
                        if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                            $testParticipant->save();
                        }

                        $testParticipant->setAttribute('score', $score);
                    }
                }
            }
        } elseif (Arr::has($payload, 'n_term') && Arr::has($payload, 'pass_mark') || ($testTake->getAttribute('n_term') !== null && $testTake->getAttribute('pass_mark') !== null)) {
            $nTerm = (Arr::has($payload, 'n_term')) ? Arr::get($payload, 'n_term') : $testTake->getAttribute('n_term');
            $passMark = (Arr::has($payload, 'pass_mark')) ? Arr::get($payload, 'pass_mark') : $testTake->getAttribute('pass_mark');
            $testTake->setAttribute('n_term', $nTerm);
            $testTake->setAttribute('pass_mark', $passMark);
            if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    if (($score / $totalScore) < ($passMark / 100)) {
                        if ($passMark <= 0) {
                            $rate = 10;
                        } elseif ($nTerm <= -3.5) {
                            $rate = 1;
                        } else {
                            $rate = 1 + $score / (($totalScore * ($passMark / 100)) / (4.5 + ($nTerm - 1)));
                        }
                    } elseif (($score / $totalScore) > ($passMark / 100)) {
                        if ($passMark >= 100) {
                            $rate = 1;
                        } elseif ($nTerm >= 5.5) {
                            $rate = 10;
                        } else {
                            $rate = 10 - (($totalScore - $score) * ((4.5 - ($nTerm - 1)) / ($totalScore - ($totalScore * ($passMark / 100)))));
                        }
                    } else {
                        $rate = (5.5 + $nTerm - 1);
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif (Arr::has($payload, 'n_term') || $testTake->getAttribute('n_term') !== null) {
            $nTerm = (Arr::has($payload, 'n_term')) ? Arr::get($payload, 'n_term') : $testTake->getAttribute('n_term');

            $testTake->setAttribute('n_term', $nTerm);
            if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = (9.0 * ($score / $totalScore)) + $nTerm;

                    if ($nTerm > 1) {
                        $scoreMin = 1.0 + ($score * (9 / $totalScore) * 2);
                        $scoreMax = 10 - (($totalScore - $score) * (9 / $totalScore) * 0.5);

                        if ($scoreMin < $scoreMax && $rate > $scoreMin) {
                            $rate = $scoreMin;
                        } elseif ($scoreMin > $scoreMax && $rate > $scoreMax) {
                            $rate = $scoreMax;
                        }
                    } elseif ($nTerm < 1) {
                        $scoreMin = 1.0 + ($score * (9 / $totalScore) * 0.5);
                        $scoreMax = 10 - (($totalScore - $score) * (9 / $totalScore) * 2);

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

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!Arr::has($payload, 'preview') || Arr::get($payload, 'preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        }

        return $testTake;
    }
}
