<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Answer;

class AnswersController extends Controller {

    /**
     * Display a listing of the answers.
     *
     * @return Response
     */
    public function index(Request $request)
    {

        $answers = Answer::filtered($request->get('filter', []), $request->get('order', []));

        if (is_array($request->get('with')) && in_array('answer_ratings', $request->get('with'))) {
            $answers->with('answerRatings', 'answerRatings.user');
        }

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($answers->get(), 200);
                break;
            case 'paginate':
            default:
                return Response::make($answers->paginate(15), 200);
                break;
        }
    }

    /**
     * Display the specified answer.
     *
     * @param  Answer  $answer
     * @return Response
     */
    public function show(Answer $answer)
    {
        $answer->load('answerParentQuestions');
        return Response::make($answer, 200);
    }

    public function showDrawing(Answer $answer)
    {
        $file = Storage::get($answer->getDrawingStoragePath());
        if ($file) {
            if (substr($file, 0, 4) ==='<svg') {
                header('Content-type: image/svg+xml');
                echo $file;
                die;
            }

            return file_get_contents($file);
        }
        abort(404);
    }

    public function getTestTake(Answer $answer)
    {
        $testTake = $answer->testParticipant->testTake;
        return Response::make($testTake, 200);
    }

}
