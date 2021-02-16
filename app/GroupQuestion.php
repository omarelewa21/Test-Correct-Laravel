<?php namespace tcCore;

use Illuminate\Support\Facades\Log;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class GroupQuestion extends Question implements QuestionInterface {

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
    protected $fillable = ['name', 'shuffle','groupquestion_type','number_of_subquestions'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $callbacks = false;

    public function duplicate(array $attributes, $ignore = null, $callbacks = true) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        $question->setAttribute('uuid', Uuid::uuid4());

        if ($question->save() === false) {
            return false;
        }

        foreach($this->groupQuestionQuestions as $groupQuestionQuestions) {
            if ($ignore instanceof Question && $ignore->getKey() == $groupQuestionQuestions->getAttribute('question_id')) {
                continue;
            }

            if ($ignore instanceof GroupQuestionQuestion && $ignore->getAttribute('group_question_id') == $groupQuestionQuestions->getAttribute('group_question_id') && $ignore->getAttribute('question_id') == $groupQuestionQuestions->getAttribute('question_id')) {
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

    public function setCallbacks($callbacks) {
        $this->callbacks = ($callbacks === true);
    }

    public function doCallbacks() {
        return $this->callbacks;
    }

    public function isParentDirty() {
        return false;
    }

    public function loadRelated($loadRelatedChildren = false, $questionIds = [])
    {
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
        if ($loadRelatedChildren === true) {
            foreach($this->groupQuestionQuestions as $groupQuestionQuestion) {
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

    public function groupQuestionQuestions() {
        return $this->hasMany('tcCore\GroupQuestionQuestion')->orderBy('order');
    }

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function reorder($movedGroupQuestionQuestion) {
        $order = $movedGroupQuestionQuestion->getAttribute('order');

        $groupQuestionQuestions = $this->groupQuestionQuestions()->orderBy('order')->get();

        $i = 1;
        if ($order) {
            foreach($groupQuestionQuestions as $groupQuestionQuestion) {
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
            foreach($groupQuestionQuestions as $groupQuestionQuestion) {
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

    public function generateAnswersForGroupQuestion($parents, $testShuffle, &$order, &$answers) {
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');

        $questions = null;
        foreach($this->groupQuestionQuestions as $groupQuestionQuestion) {
            $question = $groupQuestionQuestion->question;
            $question->setAttribute('order', $groupQuestionQuestion->getAttribute('order'));
            $question->setAttribute('maintain_position', $groupQuestionQuestion->getAttribute('maintain_position'));
            $questions[] = $question;
        }

        if ($questions === null) {
            return;
        }
        usort($questions, function($a, $b) {
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

        foreach($questions as $question) {
            $question = $question->getQuestionInstance();
            if ($question->getAttribute('maintain_position') == 1 || $this->getAttribute('shuffle') == 0 || $testShuffle == 0) {
                $questionOrder[$groupQuestionOrder] = $question;
            } else {
                $shuffleQuestions[] = $question;
                $availableOrder[] = $groupQuestionOrder;
            }
            $groupQuestionOrder++;
        }

        //Insert shuffled questions
        shuffle($shuffleQuestions);

        foreach($shuffleQuestions as $question) {
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

    public function getQuestionScores($parents, &$questionMaxScore, &$pointsPerQuestion) {
        $this->load('groupQuestionQuestions', 'groupQuestionQuestions.question');
        $parents[] = $this->getKey();
        foreach($this->groupQuestionQuestions as $groupQuestionQuestions) {
            if ($groupQuestionQuestions->question instanceof GroupQuestion) {
                if (!in_array($groupQuestionQuestions->question->getKey(), $parents)) {
                    $groupQuestionQuestions->question->getQuestionScores([], $questionMaxScore, $pointsPerQuestion);
                }
            } else {
                $questionMaxScore += $groupQuestionQuestions->question->getAttribute('score');
                $pointsPerQuestion[$groupQuestionQuestions->question->getKey()] = $groupQuestionQuestions->question->getAttribute('score');
            }
        }
        return;
    }

    public function gatherAffectedTests($ignoreGroupQuestions = [], $ignoreTests = []) {
        Log::debug('Gathering affected metadata for group-question #'.$this->getKey());
        QuestionGatherer::invalidateGroupQuestionCache($this);

        $groupQuestionId = $this->getKey();
        $tests = Test::whereIn('id', function($query) use ($groupQuestionId) {
            $testQuestion = new TestQuestion();
            $query->from($testQuestion->getTable())->select('test_id')->where('question_id', $groupQuestionId);
        })->whereNotIn('id', $ignoreTests)->get();
        $ignoreTests = $tests->pluck('id');

        $ignoreGroupQuestions[] = $this->getKey();
        $groupQuestions = static::whereIn('id', function($query) use ($groupQuestionId) {
            $testQuestion = new GroupQuestionQuestion();
            $query->from($testQuestion->getTable())->select('group_question_id')->where('question_id', $groupQuestionId);
        })->get();

        foreach($groupQuestions as $groupQuestion) {
            if (!in_array($groupQuestion->getKey(), $ignoreGroupQuestions)) {
                $groupQuestionTests = $groupQuestion->gatherAffectedTests($ignoreGroupQuestions, $ignoreTests);
                foreach($tests as $groupQuestionTest) {
                    $tests->add($groupQuestionTest);
                    $ignoreTests[] = $groupQuestionTest->getKey();
                }
            }
        }

        return $tests;
    }

    public function canCheckAnswer() {
        return false;
    }

    public function checkAnswer($answer) {
        return false;
    }


}
