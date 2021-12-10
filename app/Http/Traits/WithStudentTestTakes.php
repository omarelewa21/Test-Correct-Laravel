<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;
use tcCore\TestTake;

trait WithStudentTestTakes
{

    private function getSchedueledTestTakesForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.time_start', $orderDirection = 'ASC')
    {
        if ($paginateBy != 0) {
            return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
                ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
                ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
                ->select(
                    'test_takes.*',
                    'tests.name as test_name',
                    'tests.question_count',
                    'subjects.name as subject_name',
                    DB::raw(
                        sprintf(
                            "case when tests.test_kind_id = %d then 'true' else 'false' end as is_assignment",
                            TestKind::ASSESSMENT_TYPE
                        )
                    )
                )
                ->where('test_participants.user_id', Auth::id())
                ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
                ->where(function($query) {
                   $query->where(function ($query) {
                       // dit is voor de toetsen.
                       $query->where('test_takes.time_start', '>=', date('y-m-d'));
                       $query->whereNull('test_takes.time_end');
                    })->orWhere(function($query){
                       // dit is voor opdrachten;
                        $query->where('test_takes.time_end', '>=', now());
                    });
                })

                ->orderBy($orderColumn, $orderDirection)
                ->paginate($paginateBy);
        }
        return TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select(
                'test_takes.*',
                'tests.name as test_name',
                'subjects.name as subject_name',
                DB::raw(
                    sprintf(
                        "case when tests.test_kind_id = %d then 'true' else 'false' end as is_assignment",
                        TestKind::ASSESSMENT_TYPE
                    )
                )
            )
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->where(function($query) {
                $query->where(function ($query) {
                    // dit is voor de toetsen.
                    $query->where('test_takes.time_start', '>=', date('y-m-d'));
                    $query->whereNull('test_takes.time_end');
                })->orWhere(function($query){
                    // dit is voor opdrachten;
                    $query->where('test_takes.time_end', '>=', now());
                });
            })
            ->orderBy($orderColumn, $orderDirection )
            ->take($amount)
            ->get();
    }

    private function getRatingsForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_participants.updated_at', $orderDirection = 'desc')
    {
        if ($paginateBy != 0) {
            return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
                ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
                ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
                ->select('test_participants.rating', 'test_participants.retake_rating','test_takes.time_start', 'test_takes.retake', 'test_takes.user_id', 'test_takes.uuid as test_take_uuid', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
                ->where('test_participants.user_id', Auth::id())
                ->where('test_participants.rating', '!=', null)->orWhere('test_participants.retake_rating', '!=', null)
                ->where('test_takes.test_take_status_id', '!=', TestTakeStatus::STATUS_RATED)
                ->orderBy($orderColumn, $orderDirection)
                ->paginate($paginateBy);
        }
        return TestParticipant::leftJoin('test_takes', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'test_takes.test_id', '=', 'tests.id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select('test_participants.rating', 'test_participants.retake_rating', 'test_takes.time_start', 'test_takes.retake', 'test_takes.user_id', 'tests.name', 'tests.subject_id', 'subjects.name as subject_name')
            ->where('test_participants.user_id', Auth::id())
            ->where('test_participants.rating', '!=', null)->orWhere('test_participants.retake_rating', '!=', null)
            ->where('test_takes.test_take_status_id', '!=', TestTakeStatus::STATUS_RATED)
            ->orderBy($orderColumn, $orderDirection)
            ->take($amount)
            ->get();
    }

    public function getBgColorForTestParticipantRating($rating): string
    {
        if ($rating > 5.5) {
            return 'bg-cta-primary text-white';
        }
        if ($rating < 5.5) {
            return 'bg-all-red text-white';
        }
        return 'bg-orange base';
    }

    public function redirectToWaitingRoom($testTakeUuid)
    {
        return redirect(route('student.waiting-room', ['take' => $testTakeUuid]));
    }

    public function getTestTakeStatusTranslationString($testTake): string
    {
        $statusName = strtolower($testTake->status_name);

        if (Str::contains($testTake->status_name, ' ')) {
            $statusName = Str::of($testTake->status_name)->replaceFirst(' ', '_')->lower();
        }

        return sprintf('general.%s', $statusName);
    }

    public function getRatingToDisplay($participant): float
    {
        $rating = $participant->rating;
        if ($participant->retake_rating != null) {
            $rating = $participant->retake_rating;
        }

        str_replace('.',',',round($rating,1));

        return $rating;
    }

    public function getParticipatingClasses($testTake)
    {
        $names = $testTake->schoolClasses()->pluck('name');

        collect($names)->each(function($name, $key) use ($names) {
            if (Str::contains($name, 'guest_class')) {
                $names[$key] = 'Gast accounts';
            }
        });

        return $names;
    }
}
