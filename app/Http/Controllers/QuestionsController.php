<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use tcCore\DrawingQuestion;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\IndexQuestionsRequest;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;

class QuestionsController extends Controller {

    public function inlineimage(Request $request, $image){

        $path = storage_path(sprintf('inlineimages/%s',$image));
        if(file_exists($path)){
            echo base64_encode(file_get_contents($path));exit;
        }
        else{
            abort(404);
        }
    }

    public function index(IndexQuestionsRequest $request) {

        $filters = $request->input('filter',[]);
        $questions = Question::filtered($filters, $request->get('order', []))
            // don't show questions from the cito import
            ->where(function($query) {
                $query->where('scope', '!=', 'cito') // should be in filtered, but can't be due to the way it is build starting with an or
                ->orWhereNull('scope');
                })
            ->with(['questionAttainments', 'questionAttainments.attainment', 'tags','authors']);

        // Log::debug($questions);

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                return Response::make($questions->get(['questions.*']), 200);
                break;
            case 'list':
                return Response::make($questions->pluck('questions.question', 'questions.id'), 200);
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
//        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
//            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
//        }, 'pValue.educationLevel']);
//
//        if($question instanceof QuestionInterface) {
//            $question->loadRelated();
//        }

//        $question->transformIfNeededForTest();

        return Response::make((new QuestionHelper())->getTotalQuestion($question), 200);
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

    public function inlineImageLaravel(Request $request, $image)
    {
        if (Storage::disk('cake')->exists("questionanswers/$image")) {
            $path = Storage::disk('cake')->path("questionanswers/$image");
            return Response::file($path);
        }

        if (Storage::exists("inlineimages/$image")) {
            $path = Storage::path("questionanswers/$image");
            return Response::file($path);
        }

        abort(404);
    }
}
