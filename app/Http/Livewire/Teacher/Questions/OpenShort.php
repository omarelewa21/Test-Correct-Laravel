<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=2a60f858-3129-4903-b275-796cbce5f610&test_question_id=4a508b73-d01b-4729-be38-440f8fd76c8e

// http://test-correct.test/teacher/questions/open-short/add?owner=test&owner_id=7dfda5b2-c0fc-44c0-8ff9-e7a3c831e4a6&test_question_id=a01fd5e2-36dc-4bc1-823f-ca794e034c3f
//
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Uuid;
use tcCore\Attachment;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\QuestionHelper;
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

    public $initWithTags = [];

    public $videos = [];

    public $testName = 'test_name';

    public $testAuthors = '';

    public $subjectId;

    public $educationLevelId;

    public $action;

    protected $tags = [];

    public $questionId;

    public $pValues = [];

    public $questionIndex;

    public $attachmentsCount = 0;

    public $mcAnswerStruct = [];
    public $mcAnswerCount = 2;
    public $mcAnswerMinCount = 2;

    public $tfTrue = true;

    public $rankingAnswerStruct = [];
    public $rankingAnswerCount = 2;
    public $rankingAnswerMinCount = 2;



    public $question = [
        'add_to_database'        => 1,
        'answer'                 => '',
        'bloom'                  => '',
        'closable'               => 0,
        'decimal_score'          => 0,
        'discuss'                => 1,
        'maintain_position'      => 0,
        'miller'                 => '',
        "is_open_source_content" => 1,
        "tags"                   => [],
        'note_type'              => 'NONE',
        'order'                  => 0,
        'question'               => '',
        'rtti'                   => '',
        'score'                  => 6,
        'subtype'                => '',
        'type'                   => '',
        "attainments"            => [],
        "test_id"                => '',
        'all_or_nothing'        => false,
    ];

    protected function rules()
    {
        $rules = ['question.question' => 'required'];

        if ($this->requiresAnswer()) {
            if($this->isMultipleChoiceQuestion()) {
                $rules += [
                    'question.answers' => 'required|array|min:2',
                    'question.answers.*.score' => 'required|integer',
                    'question.answers.*.answer' => 'required',
                    'question.answers.*.order' => 'required',
                    'question.score' => 'required|integer|min:1',
                ];
            } else if($this->isTrueFalseQuestion()){
                $rules = [
                    'question.answers' => 'required|array|min:2|max:2',
                ];
            } else if($this->isRankingQuestion()){
                $rules += [
                    'question.answers' => 'required|array|min:2',
                    'question.answers.*.answer' => 'required',
                    'question.answers.*.order' => 'required',
                ];
            } else {
                $rules += ['question.answer' => 'required'];
            }
        }
        return $rules;
    }

    protected function getValidationAttributes()
    {
        return [
            'question.question' => __('cms.Vraagstelling'),
            'question.answer'   => __('cms.Antwoordmodel')
        ];
    }

    public function requiresAnswer()
    {
        return $this->isShortOpenQuestion() || $this->isMediumOpenQuestion() || $this->isMultipleChoiceQuestion() || $this->isTrueFalseQuestion() || $this->isRankingQuestion();;
    }

    protected function getListeners()
    {
        return [
            'new-tags-for-question' => 'handleTags',
            'updated-attainment'    => 'handleAttainment',
            'new-video-attachment'  => 'handleNewVideoAttachment'
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
            case 'OpenQuestion':
                if ($this->question['subtype'] == 'short') {
                    $translation = 'cms.open-question-short';
                    break;
                }
                $translation = 'cms.open-question-medium';
                break;
            case 'MultipleChoiceQuestion':
                if(Str::lower($this->question['subtype']) == 'multiplechoice') {
                    $translation = 'cms.multiplechoice-question-multiplechoice';
                }
                if(Str::lower($this->question['subtype']) == 'truefalse') {
                    $translation = 'cms.multiplechoice-question-truefalse';
                }
                break;
            case 'RankingQuestion':
               $translation = 'cms.ranking-question';
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

        $activeTest = (request()->input('owner') == 'group') ?
            TestQuestion::find(request()->input('owner_id'))->test :
            Test::whereUuid(request()->input('owner_id'))->first();



        $activeTest->load('testAuthors', 'testAuthors.user');



            // @TODO mag ik deze test zien;
            // @TODO mag ik deze testQuestion editen?
            // @TODO is deze test uberhaupt onderdeel van deze test?
            // @TODO what to do when owner is a GroupQuestion?

        $this->testName = $activeTest->name;
        $this->testAuthors = $activeTest->AuthorsAsString;
        $this->subjectId = $activeTest->subject_id;
        $this->question['test_id'] = $activeTest->id;
        $this->educationLevelId = $activeTest->education_level_id;
       // $this->question['order'] = $activeTest->testQuestions()->count()+1;

        if ($this->test_question_id) {
//            dd($this->test_question_id);
            if (request('owner') == 'group') {
                $tq = GroupQuestionQuestion::whereUuid($this->test_question_id)->first();
                $q = $tq->question;
            } else {
                $tq = TestQuestion::whereUuid($this->test_question_id)->first();
                $q = $tq->question;
            }

            if ($q) {
                $q = (new QuestionHelper())->getTotalQuestion($q->question);

                $this->pValues = $q->getQuestionInstance()->getRelation('pValue');
            }

            $this->questionId = $q->question->getKey();
            $this->question['bloom'] = $q->bloom;
            $this->question['rtti'] = $q->rtti;
            $this->question['miller'] = $q->miller;
            $this->question['answer'] = $q->answer;
            $this->question['question'] = $q->question->getQuestionHTML();
            $this->question['score'] = $q->score;
            $this->question['note_type'] = $q->note_type;
            $this->question['attainments'] = $q->getQuestionAttainmentsAsArray();
            $this->question['order'] = $tq->order;
            $this->question['all_or_nothing'] = $q->all_or_nothing;

            $this->educationLevelId = $q->education_level_id;

            $this->initWithTags = $q->tags;
            $this->attachments = $q->attachments;
            $this->attachmentsCount = count($q->attachments);

            if ($this->isCompletionQuestion()) {
                $this->question['question'] = $this->decodeCompletionTags($q);
            }

            if($this->isMultipleChoiceQuestion()){

                $this->mcAnswerStruct = $q->multipleChoiceQuestionAnswers->map(function($answer,$key){
                    return [
                        'id'    => Uuid::uuid4(),
                        'order' => $key+1,
                        'score' => $answer->score,
                        'answer'=> $answer->answer,
                    ];
                })->toArray();
            }

            if($this->isTrueFalseQuestion()){
                $q->multipleChoiceQuestionAnswers->each(function($answer){
                   if(Str::lower($answer->answer) === 'juist' && $answer->score > 0){
                       $this->tfTrue = true;
                   }
                    if(Str::lower($answer->answer) === 'onjuist' && $answer->score > 0){
                        $this->tfTrue = false;
                    }
                });
            }

            if($this->isRankingQuestion()){
                $this->rankingAnswerStruct = $q->rankingQuestionAnswers->map(function($answer,$key){
                    return [
                        'id'    => Uuid::uuid4(),
                        'order' => $key+1,
                        'answer'=> $answer->answer,
                    ];
                })->toArray();
            }
        }
        if($this->isMultipleChoiceQuestion()){
            $this->createMCAnswerStruct();
        }

        if($this->isRankingQuestion()){
            $this->createRankingAnswerStruct();
        }
    }

    public function tfIsActiveAnswer($val)
    {
        return ($this->tfTrue == ($val == 'true'));
    }

    public function updatedTfTrue($val)
    {
        $this->tfTrue = ($val == 'true');
    }

    protected function prepareMultipleChoiceQuestionTrueFalseForSave()
    {
        $result = [];
        $nr = 1;
        foreach(['Juist' => true,'Onjuist' => false] as $option => $value){
            $result[] = [
                'order' => $nr,
                'answer' => $option,
                'score' => ($this->tfTrue === $value) ? $this->question['score'] : 0,
            ];
            $nr++;
        }
        $this->question['answers'] = $result;
        unset($this->question['answer']);
    }

    // Multiple Choice
    public function updateMCOrder($value)
    {
        foreach($value as $key => $item){
            $this->mcAnswerStruct[((int) $item['value'])-1]['order'] = $item['order'];
        }

        $this->mcAnswerStruct = array_values(collect($this->mcAnswerStruct)->sortBy('order')->toArray());
        $this->createMCAnswerStruct();

    }

    public function mcCanDelete()
    {
        return $this->mcAnswerMinCount < count($this->mcAnswerStruct);
    }

    public function mcDelete($id)
    {
        if(!$this->mcCanDelete()) {
            return;
        }

        $this->mcAnswerStruct = array_values(collect($this->mcAnswerStruct)->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if($this->mcAnswerMinCount < $this->mcAnswerCount) {
            $this->mcAnswerCount--;
        }
        $this->createMCAnswerStruct();
    }

    public function mcAddAnswerItem()
    {
        $this->mcAnswerCount++;
        $this->createMCAnswerStruct();
    }

    public function mcUpdated($name,$value)
    {
        $this->createMCAnswerStruct();
    }

    public function createMCAnswerStruct()
    {
        $result = [];

        collect($this->mcAnswerStruct)->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'answer' => $value['answer'], 'score' => (int) $value['score']];
        })->toArray();

        if(count($this->mcAnswerStruct) < $this->mcAnswerCount){
            for($i = count($this->mcAnswerStruct);$i < $this->mcAnswerCount;$i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'score' => 0,
                    'answer' => ''
                ];
            }
        }

        $this->mcAnswerStruct  = $result;
        $this->mcAnswerCount = count($this->mcAnswerStruct);
    }

    protected function prepareMultipleChoiceQuestionMultipleChoiceForSave()
    {
        $this->question['answers'] = array_values(collect($this->mcAnswerStruct)->map(function($answer){
            return [
                'order' => $answer['order'],
                'answer' => $answer['answer'],
                'score' => $answer['score'],
            ];
        })->toArray());
        unset($this->question['answer']);
        $this->question['score'] = collect($this->mcAnswerStruct)->sum('score');
    }

    // Ranking

    public function updateRankingOrder($value)
    {
        foreach($value as $key => $item){
            $this->rankingAnswerStruct[((int) $item['value'])-1]['order'] = $item['order'];
        }

        $this->rankingAnswerStruct = array_values(collect($this->rankingAnswerStruct)->sortBy('order')->toArray());
        $this->createRankingAnswerStruct();

    }

    public function rankingCanDelete()
    {
        return $this->rankingAnswerMinCount < count($this->rankingAnswerStruct);
    }

    public function rankingDelete($id)
    {
        if(!$this->rankingCanDelete()) {
            return;
        }

        $this->rankingAnswerStruct = array_values(collect($this->rankingAnswerStruct)->filter(function($answer) use ($id){
            return $answer['id'] != $id;
        })->toArray());

        if($this->rankingAnswerMinCount < $this->rankingAnswerCount) {
            $this->rankingAnswerCount--;
        }
        $this->createRankingAnswerStruct();
    }

    public function rankingAddAnswerItem()
    {
        $this->rankingAnswerCount++;
        $this->createRankingAnswerStruct();
    }

    public function rankingUpdated($name,$value)
    {
        $this->createRankingAnswerStruct();
    }

    public function createRankingAnswerStruct()
    {
        $result = [];

        collect($this->rankingAnswerStruct)->each(function ($value, $key) use (&$result) {
            $result[] = (object)['id' => $value['id'], 'order' => $key + 1, 'answer' => $value['answer']];
        })->toArray();

        if(count($this->rankingAnswerStruct) < $this->rankingAnswerCount){
            for($i = count($this->rankingAnswerStruct);$i < $this->rankingAnswerCount;$i++){
                $result[] = (object)[
                    'id'    => Uuid::uuid4(),
                    'order' => $i+1,
                    'answer' => ''
                ];
            }
        }

        $this->rankingAnswerStruct  = $result;
        $this->rankingAnswerCount = count($this->rankingAnswerStruct);
    }

    protected function prepareRankingQuestionRankingForSave()
    {
        $this->question['answers'] = array_values(collect($this->rankingAnswerStruct)->map(function($answer){
            return [
                'order' => $answer['order'],
                'answer' => $answer['answer'],
            ];
        })->toArray());
        unset($this->question['answer']);
    }

    public function save()
    {

        $prepareFunction = sprintf('prepare%s%sForSave',$this->question['type'], $this->question['subtype']);
        if(method_exists($this,$prepareFunction)){
            $this->$prepareFunction();
        }
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

    public function updated($name, $value)
    {
        if($this->isMultipleChoiceQuestion() && Str::startsWith($name,'mc')){
            $this->mcUpdated($name,$value);
        }
    }

    public function isRankingQuestion()
    {
        if ($this->question['type'] !== 'RankingQuestion') {
            return false;
        }
        return true;
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
        if ($this->question['type'] !== 'CompletionQuestion') {
            return false;
        }

        return $this->question['subtype'] == 'completion';
    }

    public function isSelectionQuestion()
    {
        if ($this->question['type'] !== 'CompletionQuestion') {
            return false;
        }

        return $this->question['subtype'] == 'multi';
    }

    public function isTrueFalseQuestion()
    {
        if ($this->question['type'] !== 'MultipleChoiceQuestion') {
            return false;
        }

        return Str::lower($this->question['subtype']) == 'truefalse';
    }

    public function isMultipleChoiceQuestion()
    {
        if ($this->question['type'] !== 'MultipleChoiceQuestion') {
            return false;
        }

        return Str::lower($this->question['subtype']) == 'multiplechoice';
    }

    public function hasAllOrNothing()
    {
        return $this->isMultipleChoiceQuestion();
    }

    public function showQuestionScore()
    {
        return ! ($this->isMultipleChoiceQuestion());
    }

    private function saveNewQuestion()
    {
        return app(\tcCore\Http\Controllers\TestQuestionsController::class)->store(new CreateTestQuestionRequest($this->question));
    }

    private function handleAttachments($response)
    {
        if ($this->uploads) {
            $this->handleFileAttachments($response);
        }

        if ($this->videos) {
            $this->handleVideoAttachments($response);
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

    public function handleAttainment(array $attainments)
    {
        $this->question['attainments'] = $attainments;
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
            $this->dispatchBrowserEvent('opentab', 1);
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

    public function handleAttachmentSettingChange($data, $attachmentUuid)
    {
        $attachment = collect($this->attachments)->where('uuid', $attachmentUuid)->first();

        $currentJson = json_decode($attachment->json, true);
        $json = array_merge($currentJson, $data);

        $attachment->json = json_encode($json);

        $attachment->save();
    }

    public function handleUploadSettingChange($setting, $value, $attachmentName)
    {
        $changedSetting = [$setting => $value];

        if (array_key_exists($attachmentName, $this->audioUploadOptions)) {
            $this->audioUploadOptions[$attachmentName] = array_merge($this->audioUploadOptions[$attachmentName], $changedSetting);
            return;
        }

        $this->audioUploadOptions[$attachmentName] = $changedSetting;
    }

    public function removeAttachment($attachmentUuid)
    {
        $testQuestion = TestQuestion::whereUuid($this->test_question_id)->first();
        $attachment = Attachment::whereUuid($attachmentUuid)->first();

        $response = app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
            ->destroy($testQuestion, $attachment);

        if ($response->getStatusCode() == 200) {
            $this->attachments = collect($this->attachments)->reject(function ($attachment) use ($attachmentUuid) {
                return $attachment->uuid == $attachmentUuid;
            });
        }
        $this->attachmentsCount--;
    }

    public function removeFromUploads($tempFile)
    {
        $this->uploads = collect($this->uploads)->reject(function ($tempUpload) use ($tempFile) {
            return $tempUpload->getClientOriginalName() == $tempFile;
        })->toArray();
        $this->attachmentsCount--;
    }

    public function removeVideo($video)
    {
        $this->videos = collect($this->videos)->reject(function ($item) use ($video) {
            return $item == $video;
        })->toArray();
        $this->attachmentsCount--;
    }

    public function handleNewVideoAttachment($link)
    {
        $this->videos[] = $link;
        $this->attachmentsCount++;
    }

    private function handleFileAttachments($response): void
    {
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

    private function handleVideoAttachments($response)
    {
        collect($this->videos)->each(function ($video) use ($response) {
            $testQuestion = $response->original;
            $attachementRequest = new  CreateAttachmentRequest([
                "type" => "video",
                "link" => $video
            ]);

            $response = app(\tcCore\Http\Controllers\TestQuestions\AttachmentsController::class)
                ->store($testQuestion, $attachementRequest);
        });
    }

    public function removeItem($item, $id)
    {
        if ($item === 'attachment') {
            $this->removeAttachment($id);
        }
        if ($item === 'upload') {
            $this->removeFromUploads($id);
        }
        if ($item === 'video') {
            $this->removeVideo($id);
        }
        if ($item === 'question') {
            $this->removeQuestion();
        }
    }

    private function removeQuestion()
    {
        if (!$this->questionId) {
            $this->returnToTestOverview();
        }

        $testQuestion = TestQuestion::whereUuid($this->test_question_id)->firstOrFail();

        $response = app(\tcCore\Http\Controllers\TestQuestionsController::class)
            ->destroy($testQuestion);

        if ($response->getStatusCode() == 200) {
            $this->returnToTestOverview();
        }
    }

    public function updatedUploads($value)
    {
        $this->attachmentsCount++;
    }

    private function decodeCompletionTags($question)
    {
        if (!$question->completionQuestionAnswers) {
            return $question->getQuestionHtml();
        }

        $tags = [];
        $question->completionQuestionAnswers->each(function ($tag) use (&$tags) {
            $tags[$tag['tag']][] = $tag['answer'];
        });

        $searchPattern = '/\[([0-9]+)\]/i';
        $replacementFunction = function ($matches) use ($question, $tags) {
            $tag_id = $matches[1]; // the completion_question_answers list is 1 based
            if (isset($tags[$tag_id])) {
                return sprintf('[%s]', implode('|', $tags[$tag_id]));
            }
        };

        return preg_replace_callback($searchPattern, $replacementFunction, $question->getQuestionHtml());
    }
}
