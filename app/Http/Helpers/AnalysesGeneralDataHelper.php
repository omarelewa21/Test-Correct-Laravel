<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\Attainment;
use tcCore\PValue;
use tcCore\PValueAttainment;
use tcCore\Question;
use tcCore\Rating;
use tcCore\Scopes\ArchivedScope;
use tcCore\Subject;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\User;

class AnalysesGeneralDataHelper
{
    public $subject = null;
    public $attainment = null;
    public $filters = [];
    public $user;

    private $filterColumn = [
        'educationLevelYears' => 'education_level_year',
        'periods'             => 'period_id',
        'teachers'            => 'author_id',
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
    }
    public function getAllForSubject(Subject $subject, $filters)
    {
        $this->subject = $subject;
        $this->filters = collect($filters)->filter()->toArray();

        return PValue::query()->addSelect(
            $this->getRaw()
        )
            ->selectSub($this->averageRatingForSubject(false), 'tests_rating_average')
            ->selectSub($this->averageRatingForSubject(true), 'assignments_rating_average')
            ->join('test_participants', 'test_participants.id', '=', 'p_values.test_participant_id')
            ->join('test_takes', 'test_takes.id', '=', 'test_participants.test_take_id')
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('p_values.subject_id', $this->subject->getKey())
            ->where('test_participants.user_id', $this->user->getKey())
            ->when(!empty($this->filters), function ($query) {
                foreach ($this->filters as $filter => $values) {
                    if (isset($this->filterColumn[$filter])) {
                        $query->whereIn($this->filterColumn[$filter], $values);
                    }
                }
            })
            ->first()
            ->toArray();
    }
    public function getAllForAttainment($attainment, $filters)
    {
        $this->attainment = $attainment;
        $this->filters = collect($filters)->filter()->toArray();
        return PValueAttainment::query()->addSelect(
            $this->getRaw()
        )
            ->selectSub($this->averageRatingForAttainment(false), 'tests_rating_average')
            ->selectSub($this->averageRatingForAttainment(true), 'assignments_rating_average')
            ->join('p_values', 'p_value_attainments.p_value_id', '=', 'p_values.id')
            ->join('test_participants', 'test_participants.id', '=', 'p_values.test_participant_id')
            ->join('test_takes', 'test_takes.id', '=', 'test_participants.test_take_id')
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('p_value_attainments.attainment_id', $this->attainment->getKey())
            ->where('test_participants.user_id', $this->user->getKey())
            ->when(!empty($this->filters), function ($query) {
                foreach ($this->filters as $filter => $values) {
                    if (isset($this->filterColumn[$filter])) {
                        $query->whereIn($this->filterColumn[$filter], $values);
                    }
                }
            })
            ->first()
            ->toArray();
    }

    private function getRaw()
    {
        return DB::raw('
            avg(
    			case when test_kind_id = 4
    				then score/max_score
    				else NULL
    			end
    			) as assignments_pvalue_average,   
    		sum(
    			case when test_kind_id = 4
    				then 1
    				else 0
    			end
    		) as assignment_question_count,
            avg(
    			case when test_kind_id <> 4
    				then score/max_score
    				else null
    			end
    			) as tests_pvalue_average,
    		sum(
    			case when test_kind_id <> 4
    				then 1
    				else 0
    			end
    		) as tests_questions,
    		count(distinct case when test_kind_id <> 4 then test_take_id end) as tests_taken,
    		count(distinct case when test_kind_id = 4 then test_take_id end) as assignments_taken
            ');
    }
    private function testAndTestTakesQuery($assignment)
    {
        $operator = $assignment ? '=' : '<>';
        return TestTake::withoutGlobalScope(ArchivedScope::class)
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('tests.subject_id', $this->subject->getKey())
            ->where('tests.test_kind_id', $operator, TestKind::ASSIGNMENT_TYPE)
            ->when(!empty($this->filters), function ($query) {
                foreach ($this->filters as $key => $value) {
                    $query->whereIn('tests.' . $this->filterColumn[$key], $value);
                }
            });
    }

    private function averageRatingForSubject(bool $assignment)
    {
        return Rating::select(DB::raw('SUM(`rating` * `weight`) / SUM(`weight`) AS rating_average'))
            ->whereIn(
                'test_participant_id',
                TestParticipant::select('id')
                    ->whereIn('test_participants.test_take_id', $this->testAndTestTakesQuery($assignment)->select('test_takes.id'))
                    ->whereUserId($this->user->getKey())
            )
            ->whereSubjectId($this->subject->getKey());
    }

    private function averageRatingForAttainment($assignment)
    {
        return $this->joinedPvalueQueryForAttainment($assignment)
            ->join('ratings', 'ratings.test_participant_id', '=', 'test_participants.id')
            ->select(DB::raw('SUM(ratings.`rating` * ratings.`weight`) / SUM(ratings.`weight`) AS rating_average'));
    }
    private function joinedPvalueQueryForAttainment($assignment)
    {
        $operator = $assignment ? '=' : '<>';
        return PValueAttainment::leftJoin('p_values', 'p_value_attainments.p_value_id', '=', 'p_values.id')
            ->leftJoin('test_participants', 'test_participants.id', '=', 'p_values.test_participant_id')
            ->leftJoin('test_takes', 'test_takes.id', '=', 'test_participants.test_take_id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('p_value_attainments.attainment_id', $this->attainment->getKey())
            ->where('test_participants.user_id', $this->user->getKey())
            ->where('tests.test_kind_id', $operator, TestKind::ASSIGNMENT_TYPE)
            ->when(!empty($this->filters), function ($query) {
                foreach ($this->filters as $key => $value) {
                    $query->whereIn('tests.' . $this->filterColumn[$key], $value);
                }
            });
    }
}