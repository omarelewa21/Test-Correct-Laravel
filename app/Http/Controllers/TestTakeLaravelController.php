<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TemporaryLogin;
use tcCore\TestTake as Test;
use Ramsey\Uuid\Uuid;

class TestTakeLaravelController extends Controller
{
    use TestTakeNavigationForController;
    use WithStudentTestTakes;

    public function overview(TestTake $testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();
        if (!$testParticipant->canTakeTestTakeInPlayer()) {
            return redirect(BaseHelper::getLoginUrl());
        }

        $current = $request->get('q') ?: '1';

        $data = self::getData($testParticipant, $testTake);
        $answers = $this->getAnswers($testTake, $data, $testParticipant);

        $data = $this->applyAnswerOrderForParticipant($data, $answers);;
        $playerUrl = route('student.test-take-laravel', ['test_take' => $testTake->uuid]);

        $nav = $this->getNavigationData($data, $answers);
        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;
        $styling = $this->getCustomStylingFromQuestions($data);
        return view('test-take-overview', compact(['data', 'current', 'answers', 'playerUrl', 'nav', 'uuid', 'testParticipant', 'styling']));
    }


    public function show($testTake, Request $request)
    {
        $testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($testTake->id)->first();

        if (!$testParticipant->canTakeTestTakeInPlayer()) {
            return redirect(BaseHelper::getLoginUrl());
        }

        $data = self::getData($testParticipant, $testTake);
        $answers = $this->getAnswers($testTake, $data, $testParticipant);
        $nav = $this->getNavigationData($data, $answers);
        $data = $this->applyAnswerOrderForParticipant($data, $answers);

        $current = (int)$request->get('q') ?: 1;
        if ($current < 1) {
            $current = 1;
        } else if ($current > $nav->count()) {
            $current = $nav->count();
        }
        $request->merge(['q' => $current]);

        $uuid = $testTake->uuid;
        // todo add check or failure when $current out of bounds $data;
        $styling = $this->getCustomStylingFromQuestions($data);

        return view('test-take', compact(['data', 'current', 'answers', 'nav', 'uuid', 'testParticipant', 'styling']));
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


    /**
     * @param $data
     * @param $answers
     * @return mixed
     */
    private function getNavigationData($data, $answers)
    {
        $nav = collect($answers)->map(function ($answer, $questionUuid) use ($data) {
            $question = collect($data)->first(function ($question) use ($questionUuid) {
                return $question->uuid == $questionUuid;
            });

            $closeable = $question->closeable;
            $closeableAudio = $this->getCloseableAudio($question);
            return [
                'uuid'      => $question->uuid,
                'id'        => $question->id,
                'answer_id' => $answer['id'],
                'answered'  => $answer['answered'],
                'closeable' => $closeable,
                'closeable_audio' => $closeableAudio,
                'closed'    => $answer['closed'],
                'group'     => [
                    'id'        => $answer['group_id'],
                    'closeable' => $answer['group_closeable'],
                    'closed'    => $answer['closed_group'],
                ],
            ];
        })->toArray();
        return collect(array_values($nav));
    }

    private function applyAnswerOrderForParticipant($data, $answers)
    {
        $newData = collect([]);
        collect($answers)->each(function ($answer) use ($data, $newData) {
            $newData->push($data->first(function ($question) use ($data, $answer) {
                return $question->id == $answer['question_id'];
            }));
        });
        return $newData;
    }

    private function getCustomStylingFromQuestions($data)
    {
        return $data->map(function ($question) {
            return $question->getQuestionInstance()->styling;
        })->unique()->implode(' ');
    }

    /**
     * Quick access to test take for Student, Teacher and Invigilator
     * @param tcCore\TestTake $testTake
     */
    public function directLink($testTakeUuid)
    {
        $notification=null;
        $url=null;

        if(!UUid::isValid($testTakeUuid) || TestTake::whereUuid($testTakeUuid)->doesntExist()){
            $notification = __('teacher.test_not_found');
            return $this->redirectToCorrectTakePage($notification);
        }
        
        $user = Auth::user();
        $testTake = TestTake::whereUuid($testTakeUuid)->with('test', 'testTakeStatus')->first();
        if (!auth()->check()) {
//            session(['take' => $testTake->uuid]);
            return redirect()->route('auth.login',['directlink' => $testTakeUuid]);
        }

        if($user->isA('student')){
            // Student
            return redirect()->route('student.waiting-room', ['take' => $testTake->uuid]);
        }

        if($user->isA('teacher') && $testTake->user_id === $user->id){
            return $this->redirectTakeOwner($testTake);
        }elseif($testTake->isInvigilator($user)){
            return $this->redirectTakeInvigilator($testTake);
        }else{
            $notification = __('teacher.test_not_found');
            return $this->redirectToCorrectTakePage($notification, $url);
        }
    }

    private function redirectTakeOwner(TestTake $testTake){
        $notification=null;
        $url=null;

        if($testTake->isAssessmentType()){
            // is assignment
            if($testTake->testTakeStatus->name == 'Taking test' || $testTake->testTakeStatus->name == 'Planned'){
                $url = sprintf("test_takes/assessment_open_teacher/%s", $testTake->uuid);
            }else{
                $url = sprintf("test_takes/view/%s", $testTake->uuid);
            }
        }else{
            if($testTake->testTakeStatus->name == 'Taking test'){
                $url = "test_takes/surveillance";
            }else{
                $url = sprintf("test_takes/view/%s", $testTake->uuid);
            }
        }
        return $this->redirectToCorrectTakePage($notification, $url);
    }

    private function redirectTakeInvigilator(TestTake $testTake){
        $notification=null;
        $url=null;

        if($testTake->isAssessmentType()){
            // is assignment
            if($testTake->testTakeStatus->name == 'Taking test' || $testTake->testTakeStatus->name == 'Planned'){
                $url = sprintf("test_takes/assessment_open_teacher/%s", $testTake->uuid);
            }else{
                $notification = __('teacher.take_not_accessible_toast_for_invigilator', ['testName' => $testTake->test->name]);
            }
        }else{
            if($testTake->testTakeStatus->name == 'Planned'){
                $url = sprintf("test_takes/view/%s", $testTake->uuid);
            }elseif($testTake->testTakeStatus->name == 'Taking test'){
                $url = "test_takes/surveillance";
            }else{
                $notification = __('teacher.take_not_accessible_toast_for_invigilator', ['testName' => $testTake->test->name]);
            }
        }
        return $this->redirectToCorrectTakePage($notification, $url);
    }

    private function redirectToCorrectTakePage($notification=null, $url=null){
        if($notification){
            $options = TemporaryLogin::buildValidOptionObject('notification', [$notification => 'info']);
        }else{
            $options = TemporaryLogin::buildValidOptionObject('page', $url);
        }

        if (auth()->check()) {
            return auth()->user()->redirectToCakeWithTemporaryLogin($options);
        }else{
            return redirect()->route('auth.login');
        }
    }
}
