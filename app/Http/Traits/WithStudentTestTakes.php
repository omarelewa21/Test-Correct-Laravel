<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\TestKind;
use tcCore\TestTakeStatus;
use tcCore\TestTake;

trait WithStudentTestTakes
{

    private function getSchedueledTestTakesForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.time_start', $orderDirection = 'ASC')
    {
        $takePlannedQuery = TestTake::leftJoin('test_participants', 'test_participants.test_take_id', '=', 'test_takes.id')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'tests.subject_id')
            ->select(
                'test_takes.*',
                'tests.name as test_name',
                'tests.question_count',
                'subjects.name as subject_name',
                DB::raw(
                    sprintf(
                        "case when tests.test_kind_id = %d then 1 else 0 end as is_assignment",
                        TestKind::ASSESSMENT_TYPE
                    )
                )
            )
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->where(function ($query) {
                $query->where(function ($query) {
                    // dit is voor de toetsen.
                    $query->where('test_takes.time_start', '>=', date('y-m-d'));
                    $query->whereNull('test_takes.time_end');
                })->orWhere(function ($query) {
                    // dit is voor opdrachten;
                    $query->where('test_takes.time_end', '>=', now());
                });
            })
            ->orderBy($orderColumn, $orderDirection);

        return $paginateBy ? $takePlannedQuery->paginate($paginateBy) : $takePlannedQuery->take($amount)->get();
    }

    private function getRatingsForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.updated_at', $orderDirection = 'desc', $withNullRatings = true)
    {
        $ratedTakesQuery = TestTake::gradedTakesWithParticipantForUser(Auth::user(), $withNullRatings)
            ->select('test_takes.*', 'tests.name as test_name', 'subjects.name as subject_name')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'tests.subject_id', '=', 'subjects.id');

        return $paginateBy ? $ratedTakesQuery->orderBy($orderColumn, $orderDirection)->paginate($paginateBy) : $ratedTakesQuery->take($amount)->get();
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

    public function redirectToWaitingRoom($testTakeUuid, $origin = null)
    {
        return redirect(route('student.waiting-room', ['take' => $testTakeUuid, 'origin' => $origin]));
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

        str_replace('.', ',', round($rating, 1));

        return $rating;
    }

    public function getParticipatingClasses($testTake)
    {
        $names = $testTake->schoolClasses()->pluck('name');

        collect($names)->each(function ($name, $key) use ($names) {
            if (Str::contains($name, 'guest_class')) {
                $names[$key] = 'Gast accounts';
            }
        });

        return $names;
    }
}
