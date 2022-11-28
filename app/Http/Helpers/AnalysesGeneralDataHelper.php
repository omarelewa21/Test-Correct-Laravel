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

        return DB::query()
            ->fromSub(
                PValue::selectSub($this->takenTestsCountForSubject(false), 'tests_taken')
                    ->selectSub($this->takenTestsCountForSubject(true), 'assignments_taken')
                    ->selectSub($this->averagePValueForSubject(false), 'tests_pvalue_average')
                    ->selectSub($this->averagePValueForSubject(true), 'assignments_pvalue_average')
                    ->selectSub($this->questionCountForSubject(false), 'tests_questions')
                    ->selectSub($this->questionCountForSubject(true), 'assignments_questions')
                    ->selectSub($this->averageRatingForSubject(false), 'tests_rating_average')
                    ->selectSub($this->averageRatingForSubject(true), 'assignments_rating_average')
                , 'sub'
            )
            ->first();
    }

    private function testAndTestTakesQuery($assignment)
    {
        $operator = $assignment ? '=' : '<>';
        return TestTake::withoutGlobalScope(ArchivedScope::class)
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->where('tests.subject_id', $this->subject->getKey())
            ->where('tests.test_kind_id', $operator, TestKind::ASSESSMENT_TYPE)

            ->when(!empty($this->filters), function ($query) {
                foreach($this->filters as $key => $value) {
                    $query->whereIn('tests.'.$this->filterColumn[$key], $value);
                }
            });
    }

    private function takenTestsCountForSubject(bool $assignment)
    {
        return TestParticipant::selectRaw('count(*)')
            ->whereIn('test_take_id', $this->testAndTestTakesQuery($assignment)->select('test_takes.id'))
            ->whereUserId($this->user->getKey());
    }

    private function questionCountForSubject(bool $assignment)
    {
        return Answer::selectRaw('count(*)')
            ->whereIn(
                'test_participant_id',
                TestParticipant::selectRaw('id')
                    ->whereIn('test_take_id', $this->testAndTestTakesQuery($assignment)->select('test_takes.id'))
                    ->whereUserId($this->user->getKey())
            );
    }

    private function averagePValueForSubject(bool $assignment)
    {
        $questionIds = Question::select('questions.id')
            ->join('test_questions', 'test_questions.question_id', '=', 'questions.id')
            ->join('tests', 'tests.id', '=', 'test_questions.test_id')
            ->whereIn('tests.id', $this->testAndTestTakesQuery($assignment)->select('tests.id'));

        return PValue::selectRaw('avg(score/max_score) as score')
            ->join('test_participants', function ($join) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $this->user->getKey());
            })
            ->whereIn('question_id', $questionIds);
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

    public function getAllForAttainment($attainment, $filters)
    {
        $this->attainment = $attainment;
        $this->filters = collect($filters)->filter()->toArray();
        return DB::query()
            ->fromSub(PValue::selectSub($this->takenTestsForAttainment(false), 'tests_taken')
                ->selectSub($this->takenTestsForAttainment(true), 'assignments_taken')
                ->selectSub($this->questionCountForAttainment(false), 'tests_questions')
                ->selectSub($this->questionCountForAttainment(true), 'assignments_questions')
                ->selectSub($this->averagePvalueForAttainment(false), 'tests_pvalue_average')
                ->selectSub($this->averagePvalueForAttainment(true), 'assignments_pvalue_average')
                ->selectSub($this->averageRatingForAttainment(false), 'tests_rating_average')
                ->selectSub($this->averageRatingForAttainment(true), 'assignments_rating_average')
                , 'sub')
            ->first();
    }

    private function takenTestsForAttainment($assignment)
    {
        return $this->joinedPvalueQueryForAttainment($assignment)
            ->selectRaw('count(distinct test_takes.id)');
    }

    private function questionCountForAttainment($assignment)
    {
        return $this->joinedPvalueQueryForAttainment($assignment)
            ->selectRaw('count(distinct p_values.question_id)');
    }

    private function averagePvalueForAttainment($assignment)
    {
        return $this->joinedPvalueQueryForAttainment($assignment)
            ->selectRaw('avg(p_values.score/p_values.max_score) as pv_score');
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
            ->where('tests.test_kind_id', $operator, TestKind::ASSESSMENT_TYPE)

            ->when(!empty($this->filters), function ($query) {
                foreach($this->filters as $key => $value) {
                    $query->whereIn('tests.'.$this->filterColumn[$key], $value);
                }
            });
    }


}