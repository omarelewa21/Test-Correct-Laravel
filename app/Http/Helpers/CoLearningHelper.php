<?php

namespace tcCore\Http\Helpers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\AnswerRating;
use tcCore\DiscussingParentQuestion;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeQuestion;

class CoLearningHelper extends BaseHelper
{
    static $test_take_id;
    static $discussing_question_id;

    public static function getTestParticipantsWithStatusAndAbnormalities($testTakeId, $discussingQuestionId)
    {
        return CoLearningHelper::buildTestParticipantsQuery($testTakeId, $discussingQuestionId)->get();
    }

    public static function nextQuestion(TestTake $testTake, ?TestParticipant $testParticipant = null)
    {
        return self::nextQuestionRefactor($testTake, $testParticipant);
    }

    public static function getTestTakeQuestionsOrdered(TestTake $testTake, $withTrashed = false)
    {
        $orderList = collect($testTake->test->getQuestionOrderListWithDiscussionType());


        $testTakeQuestions = TestTakeQuestion::whereTestTakeId($testTake->getKey())
            ->when(value   : $withTrashed,
                   callback: fn($query) => $query->withTrashed()
            )
            ->get()
            ->sortBy(fn($item) => $orderList->get($item->question_id)["order"]);

        return $testTakeQuestions;
    }

    private static function buildTestParticipantsQuery($testTakeId, $discussingQuestionId)
    {
        static::$test_take_id = $testTakeId;
        static::$discussing_question_id = $discussingQuestionId;
        $heartbeatStillActiveThresholdDatetime = now()->subSeconds(30)->format('Y-m-d H:i:s');

        return TestParticipant::where('test_participants.test_take_id', static::$test_take_id)
            ->selectRaw(
                sprintf(
                    'test_participants.*, 
                    CASE WHEN heartbeat_at >= "%s" THEN 1 ELSE 0 END as active,
                    CONVERT(SUM(answers.done = 1), SIGNED) as answer_to_rate,
                    CONVERT(SUM(answer_ratings.rating IS NOT null), SIGNED) as answer_rated',
                    $heartbeatStillActiveThresholdDatetime
                )
            )->addSelect('abnormalities.abnormalities')
            ->join('answer_ratings', 'answer_ratings.user_id', '=', 'test_participants.user_id')
            ->join('answers', 'answer_ratings.answer_id', '=', 'answers.id')
            ->joinSub(
                query   : CoLearningHelper::getAbnormalitiesQuery(),
                as      : 'abnormalities',
                first   : 'abnormalities.user_id',
                operator: '=',
                second  : 'test_participants.user_id'
            )->where('answers.question_id', '=', static::$discussing_question_id)
            ->where('test_participants.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', AnswerRating::TYPE_STUDENT)
            ->where('answer_ratings.deleted_at', '=', null)
            ->where('answers.deleted_at', '=', null)
            ->where('test_participants.deleted_at', '=', null)
            ->groupBy('test_participants.id');
    }


    private static function getAbnormalitiesQuery(): Builder
    {
        return DB::query()->selectRaw('total.user_id, sum(total.abnormalities) as abnormalities')
            ->fromSub(function ($query) {
                $query->selectRaw(
                    'student_ratings.answer_id, student_ratings.user_id, CASE
                    WHEN teacher_rating IS NOT NULL THEN if(student_rating != teacher_rating, 1, 0)
                    WHEN system_rating IS NOT NULL THEN if(student_rating != system_rating, 1, 0)
                    WHEN student_abnormalities.abnormalities > 0 THEN 1
                    ELSE 0 END as abnormalities
                '
                )
                    ->fromSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_STUDENT), 'student_ratings')
                    ->joinSub(
                        static::getRatingsSubQueryPerType(AnswerRating::TYPE_TEACHER),
                        'teacher_ratings',
                        'teacher_ratings.answer_id',
                        '=',
                        'student_ratings.answer_id',
                        'left'
                    )
                    ->joinSub(
                        static::getRatingsSubQueryPerType(AnswerRating::TYPE_SYSTEM),
                        'system_ratings',
                        'system_ratings.answer_id',
                        '=',
                        'student_ratings.answer_id',
                        'left'
                    )
                    ->joinSub(
                        static::getStudentVsStudentAbnormalitiesSubQuery(),
                        'student_abnormalities',
                        function ($join) {
                            $join->on('student_abnormalities.user_id', '=', 'student_ratings.user_id');
                            $join->on('student_abnormalities.answer_id', '=', 'student_ratings.answer_id');
                        },
                        type: 'left'
                    );
            }, 'total')
            ->groupBy('total.user_id');
    }

    private static function getStudentVsStudentAbnormalitiesSubQuery(): Builder
    {
        return DB::query()->select(['student_abnormalities.user_id', 'student_abnormalities.answer_id', 'abnormalities']
        )
            ->fromSub(static::getRatingsSubQueryPerType(AnswerRating::TYPE_STUDENT, true), 'student_abnormalities')
            ->JoinSub(function ($query) {
                $query->selectRaw(
                    'answer_ratings.answer_id,
                                  CASE
                                      WHEN COUNT(
                                              DISTINCT CASE WHEN answer_ratings.`rating` IS NOT NULL THEN rating END
                                           ) > 1 THEN 1
                                      ELSE 0 
                                      END AS abnormalities'
                )
                    ->from('answers')->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
                    ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
                    ->where('answer_ratings.type', '=', AnswerRating::TYPE_STUDENT)
                    ->where('answer_ratings.deleted_at', '=', null)
                    ->groupBy('answer_ratings.answer_id');
            },
                'student_answer_abnormalities',
                'student_abnormalities.answer_id',
                '=',
                'student_answer_abnormalities.answer_id',
                'cross');
    }

    private static function getRatingsSubQueryPerType($type, $excludeRating = false): Builder
    {
        if (!is_string($type) || !in_array(
                $type = Str::upper($type),
                [AnswerRating::TYPE_STUDENT, AnswerRating::TYPE_TEACHER, AnswerRating::TYPE_SYSTEM]
            )) {
            throw new \Exception(sprintf("Rating type %s doesn't exist", $type));
        }

        $selectColumnNames = collect([
            'answer_ratings.answer_id',
        ])->when(
            $type === AnswerRating::TYPE_STUDENT,
            fn($collection) => $collection->push('answer_ratings.user_id')
        )->when(
            $excludeRating === false,
            fn($collection) => $collection->push(sprintf('answer_ratings.rating as %s_rating', Str::lower($type)))
        )->toArray();


        return DB::query()
            ->select($selectColumnNames)
            ->from('answers')
            ->crossJoin('answer_ratings', 'answers.id', '=', 'answer_ratings.answer_id')
            ->where('answer_ratings.test_take_id', '=', static::$test_take_id)
            ->where('answer_ratings.type', '=', $type)
            ->where('answer_ratings.deleted_at', '=', null);
    }

    protected static function nextQuestionRefactor(TestTake $testTake, ?TestParticipant $selfPacingTestParticipant = null): bool|TestTake
    {
        if ($testTake->testTakeStatus->name !== 'Discussing') {
            return false;
        }
        $testTake->load([
            'discussingParentQuestions'              => fn($query) => $query->orderBy('level'),
            'testParticipants',
            'testParticipants.answers:id,test_participant_id,uuid,done,question_id',
            'testParticipants.answers.answerRatings' => fn($query) => $query->where('type', 'STUDENT'),
            'testParticipants.answers.answerParentQuestions',
            'testTakeQuestions',
        ]);
        // Set next question
        $newQuestionIdParents = QuestionGatherer::getNextQuestionId(
            $testTake->getAttribute('test_id'),
            $testTake->getDottedDiscussingQuestionIdWithOptionalGroupQuestionId($selfPacingTestParticipant),
            false,//$testTake->isDiscussionTypeOpenOnly(),
            skipDoNotDiscuss: true,
            testTakeId: $testTake->getKey(),
        );

        // If no next question present => quit;
        $testTake->discussingParentQuestions()->delete();
        if ($newQuestionIdParents === false) {
            $testTake->setAttribute('discussing_question_id', null);
            if (!$testTake->save()) {
                throw new \Exception('Failed to update test take');
            }
            return $testTake;
        }

        return self::createAnswerRatingsForDiscussingQuestion($newQuestionIdParents, $testTake, $selfPacingTestParticipant);
    }

    /**
     * @param int|string $newQuestionIdParents //dottedQuestionId: (string) groupId.questionId or (int) questionId
     * @param TestTake $testTake
     * @param TestParticipant|null $selfPacingTestParticipant
     * @return TestTake
     * @throws \Exception
     */
    public static function createAnswerRatingsForDiscussingQuestion(int|string $newQuestionIdParents, TestTake $testTake, ?TestParticipant $selfPacingTestParticipant): TestTake
    {

        $newQuestionIdParentParts = explode('.', $newQuestionIdParents);
        $newQuestionId = array_pop($newQuestionIdParentParts);
        $nextQuestionToDiscuss = QuestionGatherer::getQuestionOfTest(
            $testTake->getAttribute('test_id'),
            $newQuestionIdParents,
            true
        );
        $discuss = $nextQuestionToDiscuss instanceof Question && $nextQuestionToDiscuss->getAttribute('discuss');
        $selfPacingTestParticipant
            ? $selfPacingTestParticipant->update(['discussing_question_id' => $newQuestionId])
            : $testTake->setAttribute('discussing_question_id', (int)$newQuestionId);

        $level = 1;

        $discussingParentQuestions = [];
        foreach ($newQuestionIdParentParts as $newQuestionIdParent) {
            $discussingParentQuestion = new DiscussingParentQuestion();
            $discussingParentQuestion->setAttribute('level', $level);
            $discussingParentQuestion->setAttribute('group_question_id', $newQuestionIdParent);
            $discussingParentQuestions[] = $discussingParentQuestion;
        }

        $testTake->discussingParentQuestions()->saveMany($discussingParentQuestions);

        if (!$testTake->save()) {
            throw new \Exception('Cannot save test take');
        }

//         Generate for active students next answer_ratings
        if (!$discuss) {
            throw new \Exception('This should be impossible');
        }

        $parents = implode('.', $newQuestionIdParentParts);
        $answerToRate = [];
        $testParticipantUserIds = [];
        $temp = [];
        foreach ($testTake->testParticipants as $testParticipant) {
            foreach ($testParticipant->answers as $answer) {
                $temp[] = $answer->getAttribute('question_id');
                if ($answer->getAttribute('question_id') != $newQuestionId) {
                    continue;
                }

                if ($answer->answerRatings->filter(fn($ar) => $ar->type === 'STUDENT')->isNotEmpty()) {
                    continue;
                }

                $answerParents = null;
                foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                    if ($answerParents !== null) {
                        $answerParents .= '.';
                    }
                    $answerParents .= $answerParentQuestion->getAttribute('group_question_id');
                }

                if ($parents != $answerParents) {
                    continue;
                }

                $answerToRate[$testParticipant->getKey()] = $answer;
                $testParticipantUserIds[$testParticipant->getKey()] = $testParticipant->getAttribute('user_id');
            }
        }
        /* When there are already student answer ratings created, just go away */
        if (empty($answerToRate)) {
            return $testTake;
        }

        $shuffledTestParticipants = array_keys($answerToRate);
        shuffle($shuffledTestParticipants);
        $shuffledAnswers = array();
        foreach ($shuffledTestParticipants as $shuffledTestParticipant) {
            $shuffledAnswers[] = $answerToRate[$shuffledTestParticipant];
        }
        $shuffledAnswers = array_combine($shuffledTestParticipants, $shuffledAnswers);

        $answerPerTestParticipant = count($answerToRate);
        if ($answerPerTestParticipant > 2) {
            $answerPerTestParticipant = 2;
        }

        $firstAssignedAnswers = [];
        $ratingsToCreate = collect([]);
        for ($i = 0; $i < $answerPerTestParticipant; $i++) {
            $values = array_values($shuffledAnswers);
            array_push($values, array_shift($values));
            $shuffledAnswers = array_combine(array_keys($shuffledAnswers), $values);

            foreach ($shuffledAnswers as $testParticipant => $answer) {
                $ratingsToCreate->push([
                    'answer_id' => $answer->getKey(),
                    'user_id'   => $testParticipantUserIds[$testParticipant],
                    'type'      => AnswerRating::TYPE_STUDENT
                ]);

                if (!array_key_exists($testParticipant, $firstAssignedAnswers)) {
                    $firstAssignedAnswers[$testParticipant] = $answer->getKey();
                }
            }
        }

        if ($ratingsToCreate->isNotEmpty()) {
            $queryInsertValues = $ratingsToCreate->map(function ($item) use ($testTake) {
                return sprintf(
                    '(%s,%s,"%s",%s,%s,%s)',
                    $item['answer_id'],
                    $item['user_id'],
                    AnswerRating::TYPE_STUDENT,
                    $testTake->getKey(),
                    'NOW()',
                    'NOW()'
                );
            })->join(',');

            DB::statement(
                'insert into `answer_ratings` (`answer_id`,
                              `user_id`,
                              `type`,
                              `test_take_id`,
                              `updated_at`,
                              `created_at`)
                        values ' . $queryInsertValues
            );
        }
        return $testTake;
    }
}


