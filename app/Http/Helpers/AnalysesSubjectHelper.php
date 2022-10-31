<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\PValue;
use tcCore\Question;
use tcCore\Rating;
use tcCore\Scopes\ArchivedScope;
use tcCore\Subject;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\User;

class AnalysesSubjectHelper
{
    public $subject;
    public $user;

    public function __construct(Subject $subject, User $user)
    {
        $this->subject = $subject;
        $this->user = $user;
    }

    public function getAll()
    {
        return DB::query()
            ->fromSub(
                PValue::selectSub($this->takenTestsCount(false), 'tests_taken')
                    ->selectSub($this->takenTestsCount(true), 'assignments_taken')

                    ->selectSub($this->averagePValue(false), 'tests_pvalue_average')
                    ->selectSub($this->averagePValue(true), 'assignments_pvalue_average')

                    ->selectSub($this->questionCount(false), 'tests_questions')
                    ->selectSub($this->questionCount(true), 'assignments_questions')

                    ->selectSub($this->averageRating(false), 'tests_rating_average')
                    ->selectSub($this->averageRating(true), 'assignments_rating_average')
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
            ->where('tests.test_kind_id', $operator, TestKind::ASSESSMENT_TYPE);
    }

    private function takenTestsCount(bool $assignment)
    {
        return TestParticipant::selectRaw('count(*)')
            ->whereIn('test_take_id', $this->testAndTestTakesQuery($assignment)->select('test_takes.id'))
            ->whereUserId($this->user->getKey());
    }

    private function questionCount(bool $assignment)
    {
        return Answer::selectRaw('count(*)')
            ->whereIn(
                'test_participant_id',
                TestParticipant::selectRaw('id')
                    ->whereIn('test_take_id', $this->testAndTestTakesQuery($assignment)->select('test_takes.id'))
                    ->whereUserId($this->user->getKey())
            );
    }

    private function averagePValue(bool $assignment)
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

    private function averageRating(bool $assignment)
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
}