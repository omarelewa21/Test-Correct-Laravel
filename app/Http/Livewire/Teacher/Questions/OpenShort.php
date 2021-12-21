<?php

namespace tcCore\Http\Livewire\Teacher\Questions;
// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=2a60f858-3129-4903-b275-796cbce5f610&test_question_id=4a508b73-d01b-4729-be38-440f8fd76c8e

// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=7dfda5b2-c0fc-44c0-8ff9-e7a3c831e4a6&test_question_id=a01fd5e2-36dc-4bc1-823f-ca794e034c3f
//
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Guid\Guid;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\OpenQuestion;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestQuestion;

class OpenShort extends Component
{
    use WithFileUploads;

    public $openTab = 1;

    public $owner_id;

    public $test_question_id;

    public $uploads = [];

    public $answerEditorId;

    public $questionEditorId;

    protected $queryString = ['owner_id', 'test_question_id'];

    public $attachments = [];
//
//    protected $queryString = ['openTab' => ['except' => 1]];

    public $questionType = 'open';

    public $testName = 'test_name';

    public $subjectId;

    public $edit = false;

    protected function getListeners()
    {
        return [
            'new-tags-for-question' => 'handleTags',
            'updated-attainment'    => 'handleAttainment',
        ];
    }

    public function mount()
    {
        $this->answerEditorId = Str::uuid()->__toString();
        $this->questionEditorId = Str::uuid()->__toString();

        if (request()->input('owner') == 'test') {
            $activeTest = Test::whereUuid(request()->input('owner_id'))->first();
            $this->subjectId = $activeTest->subjectId;
            $this->question['test_id'] = $activeTest->id;

            if ($this->test_question_id) {
                $q = TestQuestion::whereUuid($this->test_question_id)->first()->question;
                $this->question['bloom'] = $q->bloom;
                $this->question['rtti'] = $q->rtti;
                $this->question['miller'] = $q->miller;
                $this->question['answer'] = $q->answer;
                $this->question['question'] = $q->question->getQuestionHTML();

                $this->edit = true;

                $this->attachments = $q->attachments;
//                dd($q)
            }
        }
//       dd($this->subjectId);
    }

    protected $tags = [];

    public $question = [
        'add_to_database'        => 1,
        'answer'                 => '',
        'bloom'                  => '',
        'closable'               => 0,
        'decimal_score'          => 0,
        'discuss'                => 1,
        'maintain_position'      => 1,
        'miller'                 => '',
        "is_open_source_content" => 1,
        "tags"                   => [
        ],
        'note_type'              => 'NONE',
        'order'                  => 0,
        'question'               => '',
        'rtti'                   => '',
        'score'                  => 6,
        'sub_type'               => 'medium',
        'type'                   => 'OpenQuestion',
        "attainments"            => [],
        "test_id"                => '',
    ];

    protected $rules = [
        'question.question' => 'required',
        'question.answer'   => 'required',
    ];

    public function save()
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->openTab = 1;
            throw ($e);
        }

        $request = [];
        if (property_exists($this, 'tags')) {
            $request['tags'] = $this->tags;
        }

        if ($this->edit) {
//            {"uri":"api-c\/test_question\/{test_question}","methods":["PUT","PATCH"],"action":{"middleware":["cakeLaravelFilter","api","dl","authorize","authorizeBinds","bindings"],"as":"test_question.update","uses":"tcCore\\Http\\Controllers\\TestQuestionsController@update","controller":"tcCore\\Http\\Controllers\\TestQuestionsController@update","namespace":"tcCore\\Http\\Controllers","prefix":"api-c","where":[]},"isFallback":false,"controller":{},"defaults":[],"wheres":[],"parameters":{"test_question":{"id":181,"created_at":"2021-12-20T12:18:19.000000Z","updated_at":"2021-12-20T12:18:19.000000Z","deleted_at":null,"test_id":116,"question_id":26,"order":2,"maintain_position":0,"discuss":1,"uuid":"26f99480-73db-4f48-b947-5b4464381c55"}},"parameterNames":["test_question"],"computedMiddleware":["cakeLaravelFilter","api","dl","authorize","authorizeBinds","bindings"],"compiled":{}}
//            $response = app(\tcCore\Http\Controllers\TestQuestionsController::class)->update($this->test_question_id, new UpdateTestQuestionRequest($this->question));
//
//            if ($response->getStatusCode() == 200) {
//                dd('gelukt');
//            }
//            dd('gefaald');
        }

        $response = app(\tcCore\Http\Controllers\TestQuestionsController::class)->store(new CreateTestQuestionRequest($this->question));
        if ($response->getStatusCode() == 200) {
            if ($this->uploads) {

                collect($this->uploads)->each(function ($upload) use ($response) {
                    $upload->store('', 'attachments');

                    $testQuestion = $response->original;
                    $attachementRequest = new  CreateAttachmentRequest([
                        "type"       => "file",
                        "title"      => $upload->getClientOriginalName(),
                        "json"       => "[]",
                        "attachment" => $upload,
                    ]);

                    $response = app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
                        ->store($testQuestion, $attachementRequest);
                });
            }

            $url = sprintf("tests/view/%s", $this->owner_id);
            $options = TemporaryLogin::buildValidOptionObject('page', $url);


            Auth::user()->redirectToCakeWithTemporaryLogin($options);
        }
    }

    public function render()
    {
        return view('livewire.teacher.questions.open-short')->layout('layouts.base');
    }

    public function handleTags($tags)
    {
        $this->question['tags'] = array_values($tags);
    }

    public function handleAttainment($attainmentId)
    {
        //  $this->question['attainments'] = [$attainmentId];
    }

    public function updatingUploads(&$value)
    {
        if (!is_array($value) && Str::contains($value, 'fake')) {
            $value = $this->uploads;
        }
    }
}
