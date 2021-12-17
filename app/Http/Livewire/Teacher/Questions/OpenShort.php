<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

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

    public $upload;

    public $answerEditorId;
    public $questionEditorId;

    protected $queryString = ['owner_id'];
//
//    protected $queryString = ['openTab' => ['except' => 1]];

    public $questionType = 'open';

    public $testName = 'test_name';

    public $subjectId;

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

        $response = app(\tcCore\Http\Controllers\TestQuestionsController::class)->store(new CreateTestQuestionRequest($this->question));
        if ($response->getStatusCode() == 200) {
            if ($this->upload) {
                $this->upload->store('','attachments');

                $testQuestion = $response->original;
                $attachementRequest = new  CreateAttachmentRequest([
                    "type"       => "file",
                    "title"      => $this->upload->getClientOriginalName(),
                    "json"       => "[]",
                    "attachment" => $this->upload,
                ]);


                $response = app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
                    ->store($testQuestion, $attachementRequest);
            }

            $url =  sprintf("tests/view/%s", $this->owner_id);
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
}
