<?php

namespace tcCore\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use tcCore\TestParticipant;
use tcCore\TestTake;

class GradesService
{
    /**
     * column 1       :     [Students]                  Lastname, Firstname (sort A-Z lastname)
     * column 2       :     [Grades]                    final grade
     * column 3 onward:     [score #questionnumber]     score for question #questionnumber
     * @param TestTake $testTake
     * @return Collection
     */
    public static function getForTestTake(TestTake $testTake) : Collection
    {
        $questionOrderList = collect($testTake->test->getQuestionOrderListExpanded());

        $testParticipants = TestParticipant::whereTestTakeId($testTake->getKey())
            ->join('users', 'users.id', '=', 'test_participants.user_id')
            ->orderBy('users.name')
            ->select([
                         'test_participants.*',
                         'users.name',
                         'users.name_first',
                         'users.name_suffix',
                     ])
            ->with('answers')
            ->get();

        $gradeListPerTestParticipant = $testParticipants->map(function ($testParticipant) use ($questionOrderList) {
            $result = [];
            $result['full_name'] = Str::squish($testParticipant->getAttribute('name') . ', ' . $testParticipant->getAttribute('name_first') . ' ' . $testParticipant->getAttribute('name_suffix'));
            $result['final_grade'] = $testParticipant->getAttribute('rating');

            if($testParticipant->answers->count() === 0) {
                $questionOrderList->each(function ($question) use (&$result) {
                    $result[$question['question_id']] = "-";
                });
                return $result;
            };

            $answers = $testParticipant->answers;
            $questionOrderList->each(function ($question) use (&$result, $answers) {
                // infoscreen then an X
                // if answer available then calculate the rating
                // if not available then check for caroussel and if so set X
                // otherwise set -
                $result[$question['question_id']] = "-";
            });



            //refactor to map result? do both steps in one map:

            $testParticipant->answers->each(function ($answer) use (&$result) {
//                if(!$answer->done ) {
//                    $result[$answer->question_id] = "answerNotDone"; // -
//                    return;
//                }
                $result[$answer->question_id] = number_format($answer->final_rating ?? $answer->calculateFinalRating(), 1, ',', '.');
            });

            foreach($result as $key => $value) {
                if(!in_array($key, ['full_name', 'final_grade'])  && strtolower($questionOrderList->get($key)['question_type']) == 'infoscreenquestion') {
                    $result[$key] = "X";
                }

                if(in_array($key, ['full_name', 'final_grade']) || !in_array($value, [null, '-'])) {
                    continue;
                }

                if($questionOrderList->get($key)['carousel_question']) {
                    $result[$key] = "X";
                }
            }

            return $result;

        });
        $questionOrderNumber = 0;
        $questionNumberTitles = $questionOrderList->map(function ($question) use (&$questionOrderNumber) {
            $questionOrderNumber++;
            return sprintf("Score #%d (%s)",$questionOrderNumber, $question['question_id']);
        })->toArray();

        $gradeListPerTestParticipant->prepend([__('test-take.Studenten'), __('test-take.grades'), ...$questionNumberTitles]);

        return $gradeListPerTestParticipant;
    }
}