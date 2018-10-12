<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use tcCore\DrawingQuestion;
use tcCore\Http\Requests;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;

class QuestionsController extends Controller {

    public function index(Request $request) {
        $questions = Question::filtered($request->get('filter', []), $request->get('order', []))->with(['questionAttainments', 'questionAttainments.attainment', 'authors', 'tags']);

        // Log::debug($questions);

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($questions->get(['questions.*']), 200);
                break;
            case 'list':
                return Response::make($questions->list('questions.question', 'questions.id'), 200);
                break;
            case 'paginate':
            default:
                return Response::make($questions->paginate(15, ['questions.*']), 200);
                break;
        }
    }

    /**
     * Display the specified question.
     *
     * @param  Question  $question
     * @return Response
     */
    public function show($question)
    {
        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
        }, 'pValue.educationLevel']);

        if($question instanceof QuestionInterface) {
            $question->loadRelated();
        }

        return Response::make($question, 200);
    }

    /**
     * Offers a download to the specified drawing question from storage.
     *
     * @param DrawingQuestion $drawingQuestion
     * @return Response
     */
    public function bg(DrawingQuestion $drawingQuestion)
    {
        if (File::exists($drawingQuestion->getCurrentBgPath())) {
            return Response::download($drawingQuestion->getCurrentBgPath(), $drawingQuestion->getAttribute('bg_name', null));
        } else {
            return Response::make('Drawing question background not found', 404);
        }
    }

}
