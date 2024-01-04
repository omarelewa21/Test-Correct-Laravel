<?php


namespace tcCore\Http\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\GroupQuestionQuestion;
use tcCore\TestKind;
use tcCore\TestTakeStatus;
use tcCore\TestTake;

trait WithStudentTestTakes
{

    private function getScheduledTestTakesForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.time_start', $orderDirection = 'ASC')
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
                        TestKind::ASSIGNMENT_TYPE
                    )
                )
            )
            ->where('test_participants.user_id', Auth::id())
            ->where('test_takes.test_take_status_id', '<=', TestTakeStatus::STATUS_TAKING_TEST)
            ->whereNull('test_participants.deleted_at')
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
            ->whereNull('test_participants.deleted_at')
            ->allowedRelationQuestions(auth()->user())
            ->orderBy($orderColumn, $orderDirection);


        return $paginateBy ? $takePlannedQuery->paginate($paginateBy) : $takePlannedQuery->take($amount)->get();
    }

    private function getRatingsForStudent($amount = null, $paginateBy = 0, $orderColumn = 'test_takes.updated_at', $orderDirection = 'desc', $withNullRatings = true)
    {
        $ratedTakesQuery = TestTake::gradedTakesWithParticipantForUser(Auth::user(), $withNullRatings)
            ->select('test_takes.*', 'tests.name as test_name', 'subjects.name as subject_name')
            ->leftJoin('tests', 'tests.id', '=', 'test_takes.test_id')
            ->leftJoin('subjects', 'tests.subject_id', '=', 'subjects.id')
            ->allowedRelationQuestions(auth()->user());

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

    public static function getData($testParticipant, $testTake)
    {
        return cache()->remember('data_test_take_' . $testTake->getKey(), now()->addMinutes(60), function () use ($testTake) {
            $testTake->load('test', 'test.testQuestions', 'test.testQuestions.question', 'test.testQuestions.question.attachments');
            return $testTake->test->testQuestions->flatMap(function ($testQuestion) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;
                    return $testQuestion->question->groupQuestionQuestions->map(function ($item) use($groupQuestion){
                        $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                        $item->question->belongs_to_carousel = $groupQuestion->isCarouselQuestion();
                        $item->question->discuss = $item->discuss;
                        return $item->question;
                    });
                }
                $testQuestion->question->discuss = $testQuestion->discuss;
                return collect([$testQuestion->question]);
            });
        });
    }

    public function getAnswers($testTake, $testQuestions, $testParticipant): array
    {
        $result = [];
        $testParticipant
            ->answers
            ->sortBy(function ($answer) {
                return $answer->order;
            })
            ->each(function ($answer) use ($testTake, &$result, $testQuestions) {
                $question = $testQuestions->first(function ($question) use ($answer) {
                    return $question->getKey() === $answer->question_id;
                });
                $groupId = 0;
                $groupCloseable = 0;
                if ($question->is_subquestion) {
                    $groupQuestionQuestion = GroupQuestionQuestion::select('group_question_questions.group_question_id', 'questions.closeable')
                        ->where('group_question_questions.question_id', $question->getKey())
                        ->whereIn('group_question_questions.group_question_id', function ($query) use ($testTake) {
                            $query->select('question_id')->from('test_questions')->where('test_id', $testTake->test_id);
                        })
                        ->leftJoin('group_questions', 'group_questions.id', '=', 'group_question_questions.group_question_id')
                        ->leftJoin('questions', 'questions.id', '=', 'group_questions.id')
                        ->get();
                    $groupId = $groupQuestionQuestion->first()->group_question_id;
                    $groupCloseable = $groupQuestionQuestion->first()->closeable;
                }

                $result[$question->uuid] = [
                    'id'              => $answer->getKey(),
                    'uuid'            => $answer->uuid,
                    'order'           => $answer->order,
                    'question_id'     => $answer->question_id,
                    'answer'          => $answer->json,
                    'answered'        => $answer->is_answered,
                    'closed'          => $answer->closed,
                    'closed_group'    => $answer->closed_group,
                    'group_id'        => $groupId,
                    'group_closeable' => $groupCloseable
                ];
            });
        return $result;
    }

    private function getNavigationData($data)
    {
        return collect($data)->map(function ($question) {
            $question->question = null;
            $closeableAudio = $this->getCloseableAudio($question);
            return [
                'id' => $question->id,
                'is_subquestion' => $question->is_subquestion,
                'closeable' => $question->closeable,
                'closeable_audio' => $closeableAudio
            ];
        })->toArray();
    }

    private function getCustomStylingFromQuestions($data)
    {
        return $data->map(function($question) {
            return $question->getQuestionInstance()->styling;
        })->unique()->implode(' ');
    }
}
