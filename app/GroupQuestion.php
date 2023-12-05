<?php

namespace tcCore;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use tcCore\Http\Traits\Questions\WithQuestionDuplicating;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;
use tcCore\Http\Helpers\QuestionHelper;

class GroupQuestion extends Question implements QuestionInterface
{
    use UuidTrait;
    use WithQuestionDuplicating;

    protected $casts = [
        'uuid'                   => EfficientUuid::class,
        'number_of_subquestions' => 'integer',
        'deleted_at'             => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'shuffle', 'groupquestion_type', 'number_of_subquestions'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $callbacks = false;

    public function duplicate(array $attributes, $ignore = null, $callbacks = true)
    {
        $question = $this->specificDuplication($attributes, $ignore);

        foreach ($this->groupQuestionQuestions as $groupQuestionQuestions) {
            if ($ignore instanceof Question && $ignore->getKey() == $groupQuestionQuestions->getAttribute(
                    'question_id'
                )) {
                continue;
            }

            /*
             * Replaced this conditional with the one below because the following scenario occured because of it:
             *  Create a new group question;
                Add an existing question twice;
                Change the content of one of them;
                Both of the (sub) question are removed, and only the changed one is duplicated and put back.

                26-07-22 - RR
            if ($ignore instanceof GroupQuestionQuestion &&
                $ignore->getAttribute('group_question_id') == $groupQuestionQuestions->getAttribute('group_question_id') &&
                $ignore->getAttribute('question_id') == $groupQuestionQuestions->getAttribute('question_id')
            ) {
                continue;
            }*/
            if ($ignore instanceof GroupQuestionQuestion && $ignore->getKey() == $groupQuestionQuestions->getKey()) {
                continue;
            }
            if ($groupQuestionQuestions->duplicate($question, [], false) === false) {
                return false;
            }
        }

        if ($this->doCallbacks()) {
            $tests = $question->gatherAffectedTests();
            foreach ($tests as $test) {
                $test->performMetadata();
            }
        }

        return $question;
    }

    public function setCallbacks($callbacks)
    {
        $this->callbacks = ($callbacks === true);
    }

    public function doCallbacks()
    {
        return $this->callbacks;
    }

    public function isParentDirty()
    {
        return false;
    }

    public function loadRelated($loadRelatedChildren = false, $questionIds = [])
    {
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
        if ($loadRelatedChildren === true) {
            foreach ($this->groupQuestionQuestions as $groupQuestionQuestion) {
                if ($groupQuestionQuestion->question instanceof GroupQuestion) {
                    if (!in_array($groupQuestionQuestion->question->getKey(), $questionIds)) {
                        $thisQuestionIds = $questionIds;
                        $thisQuestionIds[] = $this->getKey();
                        $groupQuestionQuestion->question->loadRelated(true, $thisQuestionIds);
                    }
                } else {
                    $groupQuestionQuestion->question->loadRelated();
                }
            }
        }
    }

    public function groupQuestionQuestions()
    {
        return $this->hasMany('tcCore\GroupQuestionQuestion')->orderBy('order');
    }

    public function question()
    {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function reorder($movedGroupQuestionQuestion)
    {
        $order = $movedGroupQuestionQuestion->getAttribute('order');

        $groupQuestionQuestions = $this->groupQuestionQuestions()->orderBy('order')->get();

        $i = 1;
        if ($order) {
            foreach ($groupQuestionQuestions as $groupQuestionQuestion) {
                if ($groupQuestionQuestion->getKey() === $movedGroupQuestionQuestion->getKey()) {
                    continue;
                }

                if ($i == $order) {
                    $i++;
                }

                $groupQuestionQuestion->doCallbacks(false);
                $groupQuestionQuestion->setAttribute('order', $i);
                $groupQuestionQuestion->save();
                $groupQuestionQuestion->doCallbacks(true);
                $i++;
            }

            if ($i < $order) {
                $movedGroupQuestionQuestion->doCallbacks(false);
                $movedGroupQuestionQuestion->setAttribute('order', $i);
                $movedGroupQuestionQuestion->save();
                $movedGroupQuestionQuestion->doCallbacks(true);
            }
        } else {
            foreach ($groupQuestionQuestions as $groupQuestionQuestion) {
                if ($groupQuestionQuestion->getKey() === $movedGroupQuestionQuestion->getKey()) {
                    continue;
                }

                $groupQuestionQuestion->doCallbacks(false);
                $groupQuestionQuestion->setAttribute('order', $i);
                $groupQuestionQuestion->save();
                $groupQuestionQuestion->doCallbacks(true);
                $i++;
            }

            $movedGroupQuestionQuestion->doCallbacks(false);
            $movedGroupQuestionQuestion->setAttribute('order', $i);
            $movedGroupQuestionQuestion->save();
            $movedGroupQuestionQuestion->doCallbacks(true);
        }
    }

    public function generateAnswersForGroupQuestion($parents, $testShuffle, &$order, &$answers)
    {
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');

        $questions = null;
        foreach ($this->groupQuestionQuestions as $groupQuestionQuestion) {
            $question = $groupQuestionQuestion->question;
            $question->setAttribute('order', $groupQuestionQuestion->getAttribute('order'));
            $question->setAttribute('maintain_position', $groupQuestionQuestion->getAttribute('maintain_position'));
            $questions[] = $question;
        }

        if ($this->isCarouselQuestion()) {
            $questions = $this->filterQuestionsForCarousel($questions);
        }

        if ($questions === null) {
            return;
        }
        usort($questions, function ($a, $b) {
            $a = $a->getAttribute('order');
            $b = $b->getAttribute('order');
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        $groupQuestionOrder = 1;
        $questionOrder = [];
        $shuffleQuestions = [];
        $availableOrder = [];

        foreach ($questions as $question) {
            $maintain_position = $question->getAttribute('maintain_position');
            $question = $question->getQuestionInstance();
            if ($maintain_position == 1 || $this->getAttribute('shuffle') == 0 || $testShuffle == 0) {
                $questionOrder[$groupQuestionOrder] = $question;
            } else {
                $shuffleQuestions[] = $question;
                $availableOrder[] = $groupQuestionOrder;
            }
            $groupQuestionOrder++;
        }

        //Insert shuffled questions
        shuffle($shuffleQuestions);

        foreach ($shuffleQuestions as $question) {
            $groupQuestionOrder = array_shift($availableOrder);

            if ($question instanceof Question) {
                $question = $question->getQuestionInstance();
                $questionOrder[$groupQuestionOrder] = $question;
            }
        }

        ksort($questionOrder);

        $parents[] = $this->getKey();

        foreach ($questionOrder as $question) {
            if ($question instanceof Question) {
                $answer = new Answer();
                $answer->setAttribute('question_id', $question->getKey());
                $answer->setAttribute('order', $order);
                $answer->setParentGroupQuestions($parents);

                $answers[] = $answer;
                $order++;
            } elseif ($question instanceof GroupQuestion) {
                if (!in_array($question->getKey(), $parents)) {
                    $question->generateAnswersForGroupQuestion($parents, $testShuffle, $order, $answers);
                }
            }
        }

        return;
    }

    // public function getQuestionScores($parents, &$questionMaxScore, &$pointsPerQuestion) {
    //     $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
    //     $parents[] = $this->getKey();
    //     foreach($this->groupQuestionQuestions as $groupQuestionQuestions) {
    //         if ($groupQuestionQuestions->question instanceof GroupQuestion) {
    //             if (!in_array($groupQuestionQuestions->question->getKey(), $parents)) {
    //                 $groupQuestionQuestions->question->getQuestionScores([], $questionMaxScore, $pointsPerQuestion);
    //             }
    //         } else {
    //             $questionMaxScore += $groupQuestionQuestions->question->getAttribute('score');
    //             $pointsPerQuestion[$groupQuestionQuestions->question->getKey()] = $groupQuestionQuestions->question->getAttribute('score');
    //         }
    //     }
    //     return;
    // }

    public function getQuestionScores($parents, &$questionMaxScore, &$pointsPerQuestion)
    {
        if ($this->groupquestion_type == 'carousel') {
            return $this->getCarouselQuestionScores($questionMaxScore, $pointsPerQuestion);
        }
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
        $parents[] = $this->getKey();
        foreach ($this->groupQuestionQuestions as $groupQuestionQuestions) {
            if ($groupQuestionQuestions->question instanceof GroupQuestion) {
                if (!in_array($groupQuestionQuestions->question->getKey(), $parents)) {
                    $groupQuestionQuestions->question->getQuestionScores([], $questionMaxScore, $pointsPerQuestion);
                }
            } else {
                $questionMaxScore += $groupQuestionQuestions->question->getAttribute('score');
                $pointsPerQuestion[$groupQuestionQuestions->question->getKey(
                )] = $groupQuestionQuestions->question->getAttribute('score');
            }
        }
        return;
    }

    private function getCarouselQuestionScores(&$questionMaxScore, &$pointsPerQuestion)
    {
        $questionMaxScore += (new QuestionHelper())->getTotalScoreForCarouselQuestion($this);
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
        foreach ($this->groupQuestionQuestions as $groupQuestionQuestions) {
            $pointsPerQuestion[$groupQuestionQuestions->question->getKey(
            )] = $groupQuestionQuestions->question->getAttribute('score');
        }
        return;
    }

    public function gatherAffectedTests($ignoreGroupQuestions = [], $ignoreTests = [])
    {
        QuestionGatherer::invalidateGroupQuestionCache($this);

        $groupQuestionId = $this->getKey();
        $tests = Test::whereIn('id', function ($query) use ($groupQuestionId) {
            $testQuestion = new TestQuestion();
            $query->from($testQuestion->getTable())->select('test_id')->where('question_id', $groupQuestionId);
        })->whereNotIn('id', $ignoreTests)->get();
        $ignoreTests = $tests->pluck('id');

        $ignoreGroupQuestions[] = $this->getKey();
        $groupQuestions = static::whereIn('id', function ($query) use ($groupQuestionId) {
            $testQuestion = new GroupQuestionQuestion();
            $query->from($testQuestion->getTable())->select('group_question_id')->where(
                'question_id',
                $groupQuestionId
            );
        })->get();

        foreach ($groupQuestions as $groupQuestion) {
            if (!in_array($groupQuestion->getKey(), $ignoreGroupQuestions)) {
                $groupQuestionTests = $groupQuestion->gatherAffectedTests($ignoreGroupQuestions, $ignoreTests);
                foreach ($tests as $groupQuestionTest) {
                    $tests->add($groupQuestionTest);
                    $ignoreTests[] = $groupQuestionTest->getKey();
                }
            }
        }

        return $tests;
    }

    public function canCheckAnswer()
    {
        return false;
    }

    public function checkAnswer($answer)
    {
        return false;
    }

    public function isCarouselQuestion(): bool
    {
        return $this->groupquestion_type === 'carousel';
    }

    public function filterQuestionsForCarousel($questions)
    {
        if (!$this->isCarouselQuestion()) {
            return $questions;
        }
        if (is_null($this->number_of_subquestions) || $this->number_of_subquestions == 0) {
            return $questions;
        }
        if ($this->number_of_subquestions >= count($questions)) {
            return $questions;
        }
        $returnArray = [];
        $randomKeys = array_rand($questions, $this->number_of_subquestions);
        if (!is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }
        foreach ($randomKeys as $randomKey) {
            $returnArray[] = $questions[$randomKey];
        }
        return $returnArray;
    }

    public function getQuestionCount()
    {
        if ($this->groupquestion_type == 'carousel') {
            return $this->getCarouselGroupQuestionCount();
        }
        return $this->getGenericGroupQuestionCount();
    }

    protected function getCarouselGroupQuestionCount()
    {
        $questionCount = $this->getGenericGroupQuestionCount();
        if ($this->number_of_subquestions > $questionCount) {
            return $questionCount;
        }
        return $this->number_of_subquestions;
    }

    protected function getGenericGroupQuestionCount()
    {
        return $this->groupQuestionQuestions()->count();
    }

    public function hasEnoughSubQuestionsAsCarousel(): bool
    {
        if (!$this->isCarouselQuestion()) {
            return true;
        }

        return $this->groupQuestionQuestions()->count() >= $this->number_of_subquestions;
    }

    public function hasEqualScoresForSubQuestions(): bool
    {
        $scores = $this->groupQuestionQuestions->map(function ($groupQuestionQuestion) {
            return $groupQuestionQuestion->question->score;
        })->unique()->count();

        return $scores <= 1;
    }

    public function getTotalScoreAttribute(): int
    {
        $questionMaxScore = 0;
        $pointsPerQuestion = [];
        $this->getQuestionScores([], $questionMaxScore, $pointsPerQuestion);
        return $questionMaxScore;
    }

    public function containsQuestionType(string $type): bool
    {
        return $this->groupQuestionQuestions()
            ->join(
                'questions',
                'questions.id',
                '=',
                'group_question_questions.question_id'
            )
            ->where('questions.type', class_basename($type))
            ->exists();
    }

    public function getConstructorErrors(bool $isDuplicate = false, bool $withTitle = false): array
    {
        if ($isDuplicate) {
            return [
                'name'    => 'duplicate_group_question',
                'message' => __('cms.duplicate_group_in_test'),
            ];
        }

        if (!$this->isCarouselQuestion()) {
            return [];
        }

        if (!$this->hasEnoughSubQuestionsAsCarousel()) {
            return [
                'name'    => 'carousel_sub_questions',
                'message' => __('cms.carousel_not_enough_questions'),
            ];
        }

        if (!$this->hasEqualScoresForSubQuestions()) {
            return $this->getEqualScoreErrorMessage($withTitle);
        }


        return [];
    }

    /**
     * @param bool $withTitle
     * @return array
     */
    private function getEqualScoreErrorMessage(bool $withTitle): array
    {
        $name = "sub_questions_scores";
        $messageKey = 'cms.carousel_subquestions_scores_differ';

        if (!$this->containsQuestionType(RelationQuestion::class)) {
            return [
                'name'    => $name,
                'message' => __($messageKey),
            ];
        }

        $error = [
            'name'    => $name . '_relation',
            'message' => __($messageKey . ($withTitle ? '_relation' : '_relation_title')),
        ];

        if (!$withTitle) {
            return $error;
        }

        return $error + ['title' => __('cms.carousel_subquestions_scores_differ_relation_title')];
    }
}
