<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use tcCore\DrawingQuestion;
use tcCore\Gates\StudentGate;
use tcCore\Gates\TeacherGate;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\IndexQuestionsRequest;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;

class QuestionsController extends Controller
{

    public function inlineimage(Request $request, $image)
    {
        if (Storage::disk('inline_images')->exists($image)) {
            $path = Storage::disk('inline_images')->path($image);
            echo base64_encode(file_get_contents($path));
            exit;
        } else {
            abort(404);
        }
    }

    public function index(IndexQuestionsRequest $request)
    {

        $filters = $request->input('filter', []);
        $questions = Question::filtered($filters, $request->get('order', []))
            // don't show questions from the cito import
            ->where(function ($query) {
                $query->where('scope', '!=',
                    'cito') // should be in filtered, but can't be due to the way it is build starting with an or
                ->orWhereNull('scope');
            })
            ->with(['questionAttainments', 'questionAttainments.attainment', 'tags', 'authors']);

        // Log::debug($questions);

        switch (strtolower($request->get('mode', 'paginate'))) {
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
     * @param Question $question
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

        if ($drawingQuestion->zoom_group) {
            return $this->drawingQuestionQuestionPng($drawingQuestion->uuid);
        } elseif (File::exists($drawingQuestion->getCurrentBgPath())) {
            return Response::download($drawingQuestion->getCurrentBgPath(),
                $drawingQuestion->getAttribute('bg_name', null));
        } else {
            return Response::make('Drawing question background not found', 404);
        }
    }

    public function inlineImageLaravel(Request $request, $image)
    {

        $questionAnswersImage = $image;
        if (!Storage::disk('cake')->exists("questionanswers/$image")) {
            $questionAnswersImage = urldecode($questionAnswersImage);
        }
        if (Storage::disk('cake')->exists("questionanswers/$questionAnswersImage")) {
            $path = Storage::disk('cake')->path("questionanswers/$questionAnswersImage");
            return Response::file($path);
        }

        $inlineImage = $image;
        if (!Storage::disk('inline_images')->exists($inlineImage)) {
            $inlineImage = urldecode($inlineImage);
        }
        if (Storage::disk('inline_images')->exists($inlineImage)) {
            $path = Storage::disk('inline_images')->path($inlineImage);
            return Response::file($path);
        }

        abort(404);
    }

    public function drawingQuestionAnswerBackgroundImage($drawingQuestion, $identifier)
    {
        $pass = false;

//        if(collect($this->getUserRoles())->contains('Teacher')) {
//            $gate = app()->make(TeacherGate::class);
//            $pass = $gate->canAccessDrawingQuestionBackgroundImage(auth()->user());
//        }
//
//        if (!$pass) {
//            return redirect()->route('auth.login');
//        }
        return $this->getDrawingQuestionBackgroundImage('answer', $drawingQuestion, $identifier);
    }

    public function drawingQuestionQuestionBackgroundImage($drawingQuestion, $identifier)
    {
        $drawingQuestion = DrawingQuestion::whereUuid($drawingQuestion)->firstOrFail();

//        if(collect($this->getUserRoles())->contains('Student')) {
//            $gate = app()->make(StudentGate::class);
//            $gate->setStudent(auth()->user());
//            $pass = $gate->canAccessDrawingQuestionQuestionBackgroundImage($drawingQuestion);
//        }
//        if(collect($this->getUserRoles())->contains('Teacher')) {
//            $gate = app()->make(TeacherGate::class);
//            $gate->setTeacher(auth()->user());
//            $pass = $gate->canAccessDrawingQuestionBackgroundImage($drawingQuestion);
//        }
//
//        if (!$pass) {
//            return redirect()->route('auth.login');
//        }

        return $this->getDrawingQuestionBackgroundImage('question', $drawingQuestion, $identifier);
    }

    private function getDrawingQuestionBackgroundImage($type, $drawingQuestion, $identifier)
    {
        $path = sprintf('%s/%s/%s', $drawingQuestion, $type, $identifier);
        if (Storage::disk(SvgHelper::DISK)->exists($path)) {
            $server = \League\Glide\ServerFactory::create([
                'source' => Storage::disk(SvgHelper::DISK)->path(sprintf('%s/%s', $drawingQuestion, $type)),
                'cache'  => Storage::disk(SvgHelper::DISK)->path(sprintf('%s/%s/cache', $drawingQuestion, $type))
            ]);

            return $server->outputImage($identifier, (new SvgHelper($drawingQuestion))->getArrayWidthAndHeight());
        }
        abort(404);
    }

    private function getPng($drawingQuestion, $fileName)
    {
        $path = sprintf('%s/%s', $drawingQuestion, $fileName);
        if (Storage::disk(SvgHelper::DISK)->exists($path)) {
            $server = \League\Glide\ServerFactory::create([
                'source' => Storage::disk(SvgHelper::DISK)->path($drawingQuestion),
                'cache'  => Storage::disk(SvgHelper::DISK)->path(sprintf('%s/cache', $drawingQuestion))
            ]);

            $widthAndHeight = (new SvgHelper($drawingQuestion))->getArrayWidthAndHeight();

            $height = (float)$widthAndHeight['h'];
            $width = (float)$widthAndHeight['w'];

            if ($width > 800) {
                $width = 800;
            }

            $height = round(800 * $height / $widthAndHeight['w']);


            $widthAndHeight['h'] = (string)$height;
            $widthAndHeight['w'] = (string)$width;

            return $server->outputImage($fileName, $widthAndHeight);
        }
        abort(404);
    }

    public function getDrawingQuestionGivenAnswerPng($answerUuid)
    {
        $path = sprintf('drawing_question_answers/%s.png', $answerUuid);
        if (Storage::exists($path)) {
            return Storage::get($path);
        }
        abort(404);
    }


    public function drawingQuestionCorrectionModelPng($drawingQuestion)
    {
        return $this->getPng($drawingQuestion, SvgHelper::CORRECTION_MODEL_PNG_FILENAME);
    }

    public function drawingQuestionQuestionPng($drawingQuestion)
    {
        return $this->getPng($drawingQuestion, SvgHelper::QUESTION_PNG_FILENAME);
    }

    public function drawingQuestionSvg($drawingQuestion)
    {
        $path = sprintf('%s/%s', $drawingQuestion, SvgHelper::SVG_FILENAME);
        if (Storage::disk(SvgHelper::DISK)->exists($path)) {
            $response = Response::make(
                (new SvgHelper($drawingQuestion))->getSvgWithUrls()
            );

            $response->header('Content-Type', 'image/svg+xml');
            return $response;
        }
        abort(404);

    }

    public function getDrawingQuestionBackgroundImageUpdated(DrawingQuestion $drawingQuestion)
    {
        return response($drawingQuestion->getBackgroundImage());
    }
}
