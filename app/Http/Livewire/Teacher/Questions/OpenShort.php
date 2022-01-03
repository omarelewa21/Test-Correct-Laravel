<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=2a60f858-3129-4903-b275-796cbce5f610&test_question_id=4a508b73-d01b-4729-be38-440f8fd76c8e

// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=7dfda5b2-c0fc-44c0-8ff9-e7a3c831e4a6&test_question_id=a01fd5e2-36dc-4bc1-823f-ca794e034c3f
//
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestQuestion;

class OpenShort extends Component
{
    use WithFileUploads;

    public $showSelectionOptionsModal = false;

    public $openTab = 1;

    public $owner_id;

    public $test_question_id;

    public $uploads = [];

    public $audioUploadOptions = [];

    public $answerEditorId;

    public $questionEditorId;

    protected $queryString = ['owner_id', 'test_question_id'];

    public $attachments = [];
//
//    protected $queryString = ['openTab' => ['except' => 1]];


    public $testName = 'test_name';

    public $testAuthors = '';

    public $subjectId;

    public $action;

    protected $tags = [];

    public $questionId;

    public $openQuestionSubType;

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
        'subtype'               => '',
        'type'                   => '',
        "attainments"            => [],
        "test_id"                => '',
    ];

    protected function rules()
    {
        $rules = collect(['question.question' => 'required',]);

        if ($this->requiresAnswer()) {
            $rules->merge([
                'question.answer' => 'required',
            ]);
        }

        return $rules->toArray();
    }

    private function requiresAnswer(){
        return $this->isShortOpenQuestion();
    }

    protected function getListeners()
    {
        return [
            'new-tags-for-question' => 'handleTags',
            'updated-attainment'    => 'handleAttainment',
            'attachment-setting-changed' => 'handleAttachmentSettingChange'
        ];
    }

    public function getQuestionTypeProperty()
    {
        switch ($this->question['type']) {
            case 'CompletionQuestion':
                if ($this->question['subtype'] == 'multi') {
                    $translation = 'cms.selection-question';
                    break;
                }
                $translation = 'cms.completion-question';
                break;
            default:
                $translation = 'cms.open-question';
                break;
        }

        return __($translation);
    }

    public function mount($action, $type, $subType)
    {
        $this->action = $action;
        $this->question['type'] = $type;
        $this->question['subtype'] = $subType;

        $this->answerEditorId = Str::uuid()->__toString();
        $this->questionEditorId = Str::uuid()->__toString();

        if (request()->input('owner') == 'test') {
            // @TODO mag ik deze test zien;
            // @TODO mag ik deze testQuestion editen?
            // @TODO is deze test uberhaupt onderdeel van deze test?
            // @TODO what to do when owner is a GroupQuestion?

            $activeTest = Test::with('testAuthors', 'testAuthors.user')->whereUuid(request()->input('owner_id'))->first();
            $this->testName = $activeTest->name;
            $this->testAuthors = $activeTest->AuthorsAsString;
            $this->subjectId = $activeTest->subjectId;
            $this->question['test_id'] = $activeTest->id;



            if ($this->test_question_id) {
                $q = TestQuestion::whereUuid($this->test_question_id)->first()->question;
                $this->questionId = $q->question->getKey();
                $this->question['bloom'] = $q->bloom;
                $this->question['rtti'] = $q->rtti;
                $this->question['miller'] = $q->miller;
                $this->question['answer'] = $q->answer;
                $this->question['question'] = $q->question->getQuestionHTML();
                $this->question['score'] = $q->score;
                $this->question['note_type'] = $q->note_type;

                $this->attachments = $q->attachments;
            }
        }
//       dd($this->subjectId);
    }

    public function save()
    {
        $this->validateAndReturnErrorsToTabOne();

        if ($this->action == 'edit') {
            $response = $this->updateQuestion();
        } else {
            $response = $this->saveNewQuestion();
        }

        if ($response->getStatusCode() == 200) {
            $this->handleAttachments($response);
        }

        $this->returnToTestOverview();
    }


    public function isShortOpenQuestion()
    {
        if ($this->question['type'] !== 'OpenQuestion') {
            return false;
        }
        return ($this->question['subtype'] === 'short');
    }

    public function isMediumOpenQuestion()
    {
        if ($this->question['type'] !== 'OpenQuestion') {
            return false;
        }
        return ($this->question['subtype'] === 'medium');
    }

    public function isCompletionQuestion()
    {
        if($this->question['type'] !== 'CompletionQuestion') return false;

        return $this->question['subtype'] == 'completion';
    }

    public function isSelectionQuestion()
    {
        if($this->question['type'] !== 'CompletionQuestion') return false;

        return $this->question['subtype'] == 'multi';
    }

    private function saveNewQuestion()
    {
        return app(\tcCore\Http\Controllers\TestQuestionsController::class)->store(new CreateTestQuestionRequest($this->question));
    }

    private function handleAttachments($response)
    {
        if ($this->uploads) {
            collect($this->uploads)->each(function ($upload) use ($response) {
                $upload->store('', 'attachments');
                $uploadJson = $this->audioUploadOptions[$upload->getClientOriginalName()] ?? [];

                $testQuestion = $response->original;
                $attachementRequest = new  CreateAttachmentRequest([
                    "type"       => "file",
                    "title"      => $upload->getClientOriginalName(),
                    "json"       => json_encode($uploadJson),
                    "attachment" => $upload,
                ]);

                $response = app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
                    ->store($testQuestion, $attachementRequest);
            });
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

    public function returnToTestOverview(): void
    {
        $url = sprintf("tests/view/%s", $this->owner_id);
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    private function validateAndReturnErrorsToTabOne()
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->openTab = 1;
            throw ($e);
        }
    }

    private function updateQuestion()
    {
        $request = new Request();
        $request->merge($this->question);

        return app(\tcCore\Http\Controllers\TestQuestionsController::class)
            ->updateFromWithin(
                TestQuestion::whereUUID($this->test_question_id)->first(),
                $request
            );
    }

    public function handleAttachmentSettingChange($data, $attachmentId)
    {
        $attachment = $this->attachments->where('id', $attachmentId)->first();

        $json = json_decode($attachment->json, true);
        $json = array_merge($json, $data);

        $attachment->json = json_encode($json);

        $attachment->save();
    }

    public function handleUploadSettingChange($setting, $value, $attachmentName)
    {
        $json = [$setting => $value];

        if (array_key_exists($attachmentName, $this->audioUploadOptions)) {
            $this->audioUploadOptions[$attachmentName] = array_merge($this->audioUploadOptions[$attachmentName], $json);
            return;
        }

        $this->audioUploadOptions[$attachmentName] = $json;
    }

    public function removeAttachment($attachmentId)
    {
        $testQuestion = TestQuestion::whereUuid($this->test_question_id)->first();
        $attachment = $this->attachments->where('id',$attachmentId)->first();

        app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
            ->destroy($testQuestion, $attachment);
    }

    public function removeFromUploads($tempFile)
    {
        $this->uploads = collect($this->uploads)->reject(function($tempUpload) use ($tempFile) {
            return $tempUpload->getClientOriginalName() == $tempFile;
        })->toArray();
    }
}
