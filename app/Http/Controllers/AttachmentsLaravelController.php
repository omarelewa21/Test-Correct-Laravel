<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Attachment;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;

class AttachmentsLaravelController extends Controller {

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(Attachment $attachment)
    {
        //Is attachment toeganklijk voor deze student

        //Zit attachment bij een vraag in deze testTake

        //Pak vragen van test van testTake

        $testParticipant = TestParticipant::whereUserId(Auth::id())->where('test_take_status_id', 3)->first();

        $testQuestions = $testParticipant->testTake->test->testQuestions->map(function ($tq, $key) use(&$questions) {
            $questions[$key] = $tq->question->id;
        });
        $question_id = $attachment->questionAttachments->where('attachment_id', $attachment->getKey())->first()->question_id;

        foreach ($questions as $key => $question) {
            if ($question == $question_id) {
                return Response::file($attachment->getCurrentPath());
            }
        }
        return Response::noContent();
    }
}
