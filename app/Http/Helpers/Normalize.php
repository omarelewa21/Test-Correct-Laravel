<?php

namespace tcCore\Http\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use tcCore\Exceptions\NormalizeException;
use tcCore\TestTake;

class Normalize
{
    public $testTake;
    private $request;
    private $ignoreQuestions;
    private $isPreview = false;
    private $scores = [];

    public function __construct(TestTake $testTake, $request)
    {
        $this->testTake = $testTake->load(['testParticipants', 'testParticipants.user' => fn($query) => $query->withTrashed()]);
        $this->request = $request;
        $this->ignoreQuestions = $request->get('ignore_questions', []);
        $this->isPreview = $this->inPreviewMode();
        $this->prepareTestTakeAttributes();
        $this->scores = $this->getParticipantScores();
    }

    private function inPreviewMode()
    {
        return $this->request->get('preview', false);
    }

    private function prepareTestTakeAttributes()
    {
        if ($this->isInRequest('ppp') || $this->isInRequest('epp') || $this->isInRequest(
                'wanted_average'
            ) || $this->isInRequest('n_term')) {
            $this->testTake->setAttribute('ppp', null);
            $this->testTake->setAttribute('epp', null);
            $this->testTake->setAttribute('wanted_average', null);
            $this->testTake->setAttribute('n_term', null);
            $this->testTake->setAttribute('pass_mark', null);
        }
    }

    private function getParticipantScores()
    {
        $scores = [];
        foreach ($this->testTake->testParticipants as $testParticipant) {
            if ($testParticipant->answers()->count() > 0) {
                $score = (float)$testParticipant->answers()->whereNotIn('question_id', $this->ignoreQuestions)->sum(
                    'final_rating'
                );
                $scores[$testParticipant->getKey()] = $score;
            }

            if (!$this->isPreview && count($this->ignoreQuestions) > 0) {
                $testParticipant->answers()->whereNotIn('question_id', $this->ignoreQuestions)->update([
                    'ignore_for_rating' => false
                ]);

                $testParticipant->answers()->whereIn('question_id', $this->ignoreQuestions)->update([
                    'ignore_for_rating' => true
                ]);
            }
        }
        return $scores;
    }

    public function isInRequest($value)
    {
        return filled($this->request->get($value, false));
    }

    public function isNotInRequest($value)
    {
        return blank($this->request->get($value, false))
            || is_null($this->testTake->getAttribute($value));
    }

    private function getTotalScore(): ?int
    {
        if (
            ($this->isNotInRequest('ppp') && $this->isInRequest('epp'))
            ||
            ($this->isNotInRequest('ppp') && $this->isNotInRequest('epp') &&
                $this->isNotInRequest('wanted_average') && $this->isInRequest('n_term'))
        ) {
            return $this->testTake->maxScore($this->ignoreQuestions);
        }
        return null;
    }

    public function normBasedOnGoodPerPoint(): Collection
    {
        $ppp = $this->isInRequest('ppp') ? $this->request->get('ppp') : $this->testTake->getAttribute('ppp');

        $this->testTake->setAttribute('ppp', $ppp);
        if (!$this->isPreview) {
            $this->saveTestTake();
        }

        if (!$ppp) {
            return collect([]);
        }

        foreach ($this->testTake->testParticipants as $testParticipant) {
            if (array_key_exists($testParticipant->getKey(), $this->scores)) {
                $score = $this->scores[$testParticipant->getKey()];
                $rate = ($score / $ppp);
                if ($rate < 1) {
                    $rate = 1;
                } elseif ($rate > 10) {
                    $rate = 10;
                }

                $testParticipant->setAttribute('rating', round($rate, 1));
                if (!$this->isPreview) {
                    $testParticipant->save();
                }

                $testParticipant->setAttribute('score', $score);
            }
        }
        return $this->testTake->testParticipants->mapWithKeys(fn($participant) => [$participant->getKey() => $participant->rating]);
    }

    public function normBasedOnErrorsPerPoint(): Collection
    {
        $epp = $this->isInRequest('epp') ? $this->request->get('epp') : $this->testTake->getAttribute('epp');
        $totalScore = $this->getTotalScore();

        $this->testTake->setAttribute('epp', $epp);
        if (!$this->isPreview) {
            $this->saveTestTake();
        }

        if (!$epp) {
            return collect([]);
        }

        foreach ($this->testTake->testParticipants as $testParticipant) {
            if (array_key_exists($testParticipant->getKey(), $this->scores)) {
                $score = $this->scores[$testParticipant->getKey()];
                $rate = 10 - (($totalScore - $score) / $epp);
                if ($rate < 1) {
                    $rate = 1;
                } elseif ($rate > 10) {
                    $rate = 10;
                }

                $testParticipant->setAttribute('rating', round($rate, 1));
                if (!$this->isPreview) {
                    $testParticipant->save();
                }

                $testParticipant->setAttribute('score', $score);
            }
        }
        return $this->testTake->testParticipants->mapWithKeys(fn($participant) => [$participant->getKey() => $participant->rating]);
    }

    public function normBasedOnAverageMark(): Collection
    {
        $average = $this->isInRequest('wanted_average') ? $this->request->get(
            'wanted_average'
        ) : $this->testTake->getAttribute('wanted_average');
        $this->testTake->setAttribute('wanted_average', $average);
        if (!$this->isPreview) {
            $this->saveTestTake();
        }

        if ($average === 1 || $average === 1.0 || is_null($average)) { // prevent division by zero error below
            return collect([]);
        }

        if ($this->scores) {
            $ppp = ((array_sum($this->scores) / count($this->scores)) / ($average - 1));
            foreach ($this->testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $this->scores)) {
                    $score = $this->scores[$testParticipant->getKey()];
                    $rate = 1 + ($score / ($ppp));
                    if ($rate < 1) {
                        $rate = 1;
                    } elseif ($rate > 10) {
                        $rate = 10;
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!$this->isPreview) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        }
        return $this->testTake->testParticipants->mapWithKeys(fn($participant) => [$participant->getKey() => $participant->rating]);
    }

    public function normBasedOnNTermAndPassMark(): Collection
    {
        $nTerm = $this->isInRequest('n_term') ? $this->request->get('n_term') : $this->testTake->getAttribute('n_term');
        $passMark = $this->isInRequest('pass_mark') ? $this->request->get('pass_mark') : $this->testTake->getAttribute(
            'pass_mark'
        );
        $totalScore = $this->getTotalScore();

        $this->testTake->setAttribute('n_term', $nTerm);
        $this->testTake->setAttribute('pass_mark', $passMark);

        if (!$this->isPreview) {
            $this->saveTestTake();
        }

        if (!$totalScore) {
            return collect([]);
        }

        foreach ($this->testTake->testParticipants as $testParticipant) {
            if (array_key_exists($testParticipant->getKey(), $this->scores)) {
                $score = $this->scores[$testParticipant->getKey()];
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
                if (!$this->isPreview) {
                    $testParticipant->save();
                }

                $testParticipant->setAttribute('score', $score);
            }
        }
        return $this->testTake->
            testParticipants->mapWithKeys(fn($participant) => [$participant->getKey() => $participant->rating]);
    }

    public function normBasedOnNTerm(): Collection
    {
        $nTerm = $this->isInRequest('n_term') ? $this->request->get('n_term') : $this->testTake->getAttribute('n_term');
        $totalScore = $this->getTotalScore();

        $this->testTake->setAttribute('n_term', $nTerm);

        if (!$this->isPreview) {
            $this->saveTestTake();
        }
        if (!$totalScore) {
            return collect([]);
        }

        foreach ($this->testTake->testParticipants as $testParticipant) {
            if (array_key_exists($testParticipant->getKey(), $this->scores)) {
                $score = $this->scores[$testParticipant->getKey()];
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
                if (!$this->isPreview) {
                    $testParticipant->save();
                }

                $testParticipant->setAttribute('score', $score);
            }
        }
        return $this->testTake->testParticipants->mapWithKeys(fn($participant) => [$participant->getKey() => $participant->rating]);
    }


    private function saveTestTake(): void
    {
        $this->testTake->setAttribute('results_published', Carbon::now());
        $this->testTake->save();
    }
}