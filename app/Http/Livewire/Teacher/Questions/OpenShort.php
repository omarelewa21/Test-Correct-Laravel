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
use tcCore\Http\Controllers\GroupQuestionQuestions\AttachmentsController as GroupAttachmentsController;
use tcCore\Http\Controllers\AttachmentsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestQuestion;

class OpenShort extends Component
{
    use WithFileUploads;

    public $showSelectionOptionsModal = false;

    public $openTab = 1;

    public $testQuestionId;

    public $testId;

    public $owner;

    public $groupQuestionQuestionId;

    public $uploads = [];

    public $audioUploadOptions = [];

    public $answerEditorId;

    public $questionEditorId;

    public $attachments = [];

    public $initWithTags = [];

    public $isPartOfGroupQuestion = false;

    protected $queryString = ['testId', 'testQuestionId', 'groupQuestionQuestionId', 'owner'];

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

    public $tfTrue = true;

    public $rankingAnswerStruct = [];
    public $rankingAnswerCount = 2;
    public $rankingAnswerMinCount = 2;

    public $cmsPropertyBag = [];

    public $question = [
        'add_to_database'        => 1,
        'answer'                 => '',
        'bloom'                  => '',
        'closeable'              => 0,
        'decimal_score'          => 0,
        'discuss'                => 1,
        'maintain_position'      => 0,
        'miller'                 => '',
        'is_open_source_content' => 1,
        'tags'                   => [],
        'note_type'              => 'NONE',
        'order'                  => 0,
        'question'               => '',
        'rtti'                   => '',
        'score'                  => 6,
        'subtype'                => '',
        'type'                   => '',
        'attainments'            => [],
        'test_id'                => '',
        'all_or_nothing'         => false,
    ];

    protected function rules()
    {
        $rules = ['question.question' => 'required'];
        if ($this->requiresAnswer()) {
            $obj = CmsFactory::create($this->question, $this);
            if ($obj) {
                $obj->mergeRules($rules);
                return $rules;

            }
            if ($this->isRankingQuestion()) {
                $rules += [
                    'question.answers'          => 'required|array|min:2',
                    'question.answers.*.answer' => 'required',
                    'question.answers.*.order'  => 'required',
                ];
            }
            $rules += ['question.answer' => 'required'];
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
        return $this->isShortOpenQuestion() || $this->isMediumOpenQuestion() || $this->isMultipleChoiceQuestion() || $this->isTrueFalseQuestion() || $this->isRankingQuestion();
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
        $obj = CmsFactory::create($this->question, $this);
        if ($obj && method_exists($obj, 'getTranslationKey')) {
            return __($obj->getTranslationKey());
        }

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
            case 'RankingQuestion':
               $translation = 'cms.ranking-question';
               break;
            default:
                $translation = 'cms.open-question';
                break;
        }

        return __($translation);
    }

    // @TODO mag ik deze test zien;
    // @TODO mag ik deze testQuestion editen?
    // @TODO is deze test uberhaupt onderdeel van deze test?
    public function mount($action, $type, $subType)
    {
        $activeTest = Test::whereUuid($this->testId)->with('testAuthors', 'testAuthors.user')->first();
        $this->initializeContext($action, $type, $subType, $activeTest);
        $this->initializePropertyBag($activeTest);
    }

    public function __call($name, $arguments)
    {
        $obj = CmsFactory::create($this->question, $this);

        if ($obj && method_exists($obj, $name) ) {
          return  $obj->$name($arguments);
        }
        return parent::__call($name, $arguments);
    }

    public function forwardToService($method, $arg = false)
    {
        $obj = CmsFactory::create($this->question, $this);

        if ($obj && is_array($method) && method_exists($obj, 'arrayCallback')) {
            return $obj->arrayCallback($method);
        }

        if ($obj && method_exists($obj, $method)) {
            if ($arg) {
                return $obj->$method($arg);
            }
            return $obj->$method();
        }

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
        $obj = CmsFactory::create($this->question, $this);
        if ($obj && method_exists($obj, 'prepareForSave')) {
            $obj->prepareForSave();
        }


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
        $obj = CmsFactory::create($this->question, $this);
        $method = 'updated'.ucfirst($name);
        if ($obj && method_exists($obj, $method)) {
            $obj->$method($value);
        }

    }

    public function isInfoscreenQuestion()
    {
        return !! (Str::lower($this->question['type']) == 'infoscreenquestion');
    }

    public function isRankingQuestion()
    {
        return !! ($this->question['type'] == 'RankingQuestion');
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
        return ! ($this->isMultipleChoiceQuestion() || $this->isInfoscreenQuestion());
    }

    private function saveNewQuestion()
    {
        if ($this->isPartOfGroupQuestion()) {
            $gqqm = GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId);
            $cgqqr = new CreateGroupQuestionQuestionRequest($this->question);

            return (new GroupQuestionQuestionsController)->store($gqqm, $cgqqr);
        }

        return (new TestQuestionsController)->store(new CreateTestQuestionRequest($this->question));
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
        $url = sprintf("tests/view/%s", $this->testId);
        if ($this->isPartOfGroupQuestion()) {
            $url = sprintf(
                'questions/view_group/%s/%s',
                $this->testId,
                $this->testQuestionId
            );
        }
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

        if ($this->isPartOfGroupQuestion()) {
            $groupQuestionQuestion = GroupQuestionQuestion::whereUuid($this->groupQuestionQuestionId)->first();
            $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId); //'577fa17d-68b7-4695-ace5-e14afd913757');

            $response = (new GroupQuestionQuestionsController)->updateFromWithin(
                $groupQuestionQuestionManager,
                $groupQuestionQuestion,
                $request
            );

        } else {
            $response = (new TestQuestionsController)->updateFromWithin(
                TestQuestion::whereUUID($this->testQuestionId)->first(),
                $request
            );
        }

        return $response;
    }

    public function isCloseableDisabled($asText = false)
    {
        if($asText){
            return 'false';
        }
        return false;
    }

    public function isMaintainPositionDisabled($asText = false)
    {
        if($asText){
            return 'false';
        }
        return false;
    }

    public function isAllowNotesDisabled($asText = false)
    {
        if($this->isInfoscreenQuestion()){
            if($asText){
                return 'true';
            }
            return true;
        }
        if($asText){
            return 'false';
        }
        return false;
    }

    public function isAddToDatabaseDisabled($asText = false)
    {
        if($this->isInfoscreenQuestion()){
            if($asText){
                return 'true';
            }
            return true;
        }
        if($asText){
            return 'false';
        }
        return false;
    }

    public function isDiscussDisabled($asText = false)
    {
        if($this->isInfoscreenQuestion()){
            if($asText){
                return 'true';
            }
            return true;
        }
        if($asText){
            return 'false';
        }
        return false;
    }

    public function isDecimalOptionDisabled($asText = false)
    {
        if($this->isInfoscreenQuestion()){
            if($asText){
                return 'true';
            }
            return true;
        }
        if($asText){
            return 'false';
        }
        return false;
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
            $this->audioUploadOptions[$attachmentName] = array_merge($this->audioUploadOptions[$attachmentName],
                $changedSetting);
            return;
        }

        $this->audioUploadOptions[$attachmentName] = $changedSetting;
    }

    public function removeAttachment($attachmentUuid)
    {
        $attachment = Attachment::whereUuid($attachmentUuid)->first();

        if ($this->isPartOfGroupQuestion()) {
            $response = (new GroupAttachmentsController)
                ->destroy(
                    GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId),
                   $attachment
                );
        } else {
            $response = (new AttachmentsController)
                ->destroy(
                    TestQuestion::whereUuid($this->testQuestionId)->first(),
                    $attachment
                );
        }

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
            $attachementRequest = new CreateAttachmentRequest([
                "type"       => "file",
                "title"      => $upload->getClientOriginalName(),
                "json"       => json_encode($uploadJson),
                "attachment" => $upload,
            ]);
            $this->createAttachementWithRequest($attachementRequest, $response);
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

            $response = $this->createAttachementWithRequest($attachementRequest, $response);
        });
    }

    public function createAttachementWithRequest(CreateAttachmentRequest $request, $response)
    {
        if ($this->isPartOfGroupQuestion()) {
            return (new GroupAttachmentsController)
                ->store(
                    GroupQuestionQuestionManager::getInstanceWithUuid($response->original->group_question_question_path),
                    $request
                );
        }
        return (new AttachmentsController)
            ->store(
                $testQuestion = $response->original,
                $request
            );
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

        $testQuestion = TestQuestion::whereUuid($this->testQuestionId)->firstOrFail();

        $response = (new TestQuestionsController)->destroy($testQuestion);

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

    public function isPartOfGroupQuestion(): bool
    {
        return $this->isPartOfGroupQuestion;
    }

    private function setIsPartOfGroupQuestion()
    {
        $this->isPartOfGroupQuestion = (request('owner') == 'group');
    }

    private function editModeForExistingQuestion()
    {
        if (empty($this->testQuestionId)) {
            return false;
        }

        if ($this->isPartOfGroupQuestion()) {
            if (empty($this->groupQuestionQuestionId)) {
                return false;
            }
        }

        return true;
    }

    private function initializeContext($action, $type, $subType, Test $activeTest): void
    {
        $this->action = $action;
        $this->question['type'] = $type;
        $this->question['subtype'] = $subType;
        $this->setIsPartOfGroupQuestion();

        $this->answerEditorId = Str::uuid()->__toString();
        $this->questionEditorId = Str::uuid()->__toString();

        $this->testName = $activeTest->name;
        $this->testAuthors = $activeTest->AuthorsAsString;
        $this->subjectId = $activeTest->subject_id;
        $this->educationLevelId = $activeTest->education_level_id;
    }

    private function initializePropertyBag($activeTest): void
    {
        $obj = CmsFactory::create($this->question, $this);

        if ($obj && method_exists($obj, 'preparePropertyBag')) {
            $obj->preparePropertyBag();
        }

        $this->question['test_id'] = $activeTest->id;

        if ($this->editModeForExistingQuestion()) {
            if ($this->isPartOfGroupQuestion()) {
                $tq = GroupQuestionQuestion::whereUuid($this->groupQuestionQuestionId)->first();
                $this->attachments = $tq->groupQuestion->attachments;
                $q = $tq->question;
            } else {
                $tq = TestQuestion::whereUuid($this->testQuestionId)->first();
                $q = $tq->question;
                $this->attachments = $q->attachments;
            }
            $q = (new QuestionHelper())->getTotalQuestion($q->question);
            $this->pValues = $q->getQuestionInstance()->getRelation('pValue');

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
            $this->question['closeable'] = $q->closeable;
            $this->question['maintain_position'] = $q->maintain_position;
            $this->question['add_to_database'] = $q->add_to_database;
            $this->question['discuss'] = $q->discuss;
            $this->question['decimal_score'] = $q->decimal_score;

            $this->educationLevelId = $q->education_level_id;

            $this->initWithTags = $q->tags;

            $this->attachmentsCount = count($this->attachments);

            if ($this->isCompletionQuestion()) {
                $this->question['question'] = $this->decodeCompletionTags($q);
            }



            if ($obj && method_exists($obj, 'initializePropertyBag')) {
                $obj->initializePropertyBag($q);
            }

            if ($this->isRankingQuestion()) {
                $this->rankingAnswerStruct = $q->rankingQuestionAnswers->map(function ($answer, $key) {
                    return [
                        'id'     => Uuid::uuid4(),
                        'order'  => $key + 1,
                        'answer' => $answer->answer,
                    ];
                })->toArray();
            }
        }

        if ($obj && method_exists($obj, 'createMCAnswerStruct')) {
            $obj->createMCAnswerStruct();
        }

        if ($this->isRankingQuestion()) {
            $this->createRankingAnswerStruct();
        }
    }
}
