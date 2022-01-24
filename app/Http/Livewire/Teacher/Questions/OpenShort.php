<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

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
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestions\AttachmentsController;
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

    public $isCloneRequest = false;

    protected $queryString = ['testId', 'testQuestionId', 'groupQuestionQuestionId', 'owner', 'isCloneRequest'];

    protected $settingsGeneralPropertiesVisibility = [
        'autoCheckAnswer' => false,
        'autoCheckAnswerCaseSensitive' => false
    ];

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

    public $cmsPropertyBag = [];

    public $rttiToggle = false;
    public $bloomToggle = false;
    public $millerToggle = false;
    public $rttiWarningShown = false;
    public $bloomWarningShown = false;
    public $millerWarningShown = false;

    public $question = [
        'add_to_database'        => 1,
        'answer'                 => '',
        'bloom'                  => '',
        'closeable'              => 0,
        'decimal_score'          => 0,
        'discuss'                => 1,
        'maintain_position'      => 0,
        'miller'                 => '',
        'is_open_source_content' => 0,
        'tags'                   => [],
        'note_type'              => 'NONE',
        'order'                  => 0,
        'question'               => '',
        'rtti'                   => '',
        'score'                  => 5,
        'subtype'                => '',
        'type'                   => '',
        'attainments'            => [],
        'test_id'                => '',
        'all_or_nothing'         => false,
    ];
    /**
     * @var CmsInfoScreen|CmsMultipleChoice|CmsOpen|CmsRanking|CmsTrueFalse|null
     */
    private $obj;

    public $sortOrderAttachments = [];


    protected function rules()
    {
        $rules = ['question.question' => 'required'];
        if ($this->obj && method_exists( $this->obj, 'mergeRules')) {
            $this->obj->mergeRules($rules);
            return $rules;
        }
        if ($this->requiresAnswer()) {
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

    protected function getMessages()
    {
        return [
            'question.rtti.required'   => __('cms.rtti warning'),
            'question.bloom.required'  => __('cms.bloom warning'),
            'question.miller.required' => __('cms.miller warning'),
        ];
    }


    public function requiresAnswer()
    {
        if ($this->obj && property_exists($this->obj, 'requiresAnswer')) {
            return $this->obj->requiresAnswer;
        }
        return false;
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
        if ($this->obj && method_exists($this->obj, 'getTranslationKey')) {
            return __($this->obj->getTranslationKey());
        }

        switch ($this->question['type']) {
            case 'OpenQuestion':
                if ($this->question['subtype'] == 'short') {
                    $translation = 'cms.open-question-short';
                    break;
                }
                $translation = 'cms.open-question-medium';
                break;
            default:
                $translation = 'cms.open-question';
                break;
        }

        return __($translation);
    }

    public function booted()
    {
        $this->obj = CmsFactory::create($this);
    }

    // @TODO mag ik deze test zien;
    // @TODO mag ik deze testQuestion editen?
    // @TODO is deze test uberhaupt onderdeel van deze test?
    public function mount($action, $type, $subType)
    {
        $activeTest = Test::whereUuid($this->testId)->with('testAuthors', 'testAuthors.user')->first();
        $this->initializeContext($action, $type, $subType, $activeTest);
        $this->obj = CmsFactory::create($this);
        $this->initializePropertyBag($activeTest);
    }

    public function __call($method, $arguments = null)
    {
        if ($this->obj && is_array($method) && method_exists($this->obj, 'arrayCallback')) {
            return $this->obj->arrayCallback($method);
        }

        if ($this->obj && method_exists($this->obj, $method) ) {
            if ($arguments) {
                return $this->obj->$method($arguments);
            }
            return $this->obj->$method();
        }

        $newName = '_'.$method;
        if (method_exists($this, $newName) ) {
            return  $this->$newName($arguments);
        }

        return parent::__call($method, $arguments);
    }

    public function save()
    {
        if ($this->obj && method_exists($this->obj, 'prepareForSave')) {
            $this->obj->prepareForSave();
        }

        $this->validateAndReturnErrorsToTabOne();

        if ($this->action == 'edit' && !$this->isCloneRequest) {
            $response = $this->updateQuestion();
        } else {
            if($this->isCloneRequest){
                $this->prepareForClone();
            }
            $response = $this->saveNewQuestion();
        }

        if ($response->getStatusCode() == 200) {
            $this->handleAttachments($response);
        }

        $this->returnToTestOverview();
    }

    protected function prepareForClone()
    {
        $this->question['order'] = 0;
        if(count($this->attachments)){
            $this->question['clone_attachments'] = collect($this->attachments)->map(function ($attachment) {
                return $attachment->uuid;
            })->toArray();
        }
    }

    public function updated($name, $value)
    {
        $method = 'updated'.ucfirst($name);
        if ($this->obj && method_exists($this->obj, $method)) {
            $this->obj->$method($value);
        }
        if($this->obj && method_exists($this->obj, 'updated')){
            $this->obj->updated($name, $value);
        }
    }

    public function showStatistics()
    {
        if($this->isCloneRequest){
            return false;
        }

        $method = 'showStatistics';
        if(method_exists($this->obj, $method)) {
            return $this->obj->$method();
        }
        return true;
    }

    protected function _showSettingsTaxonomy()
    {
        return true;
    }

    protected function _showSettingsAttainments()
    {
        return true;
    }

    protected function _showSettingsTags()
    {
        return true;
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

    public function isArqQuestion()
    {
        if ($this->question['type'] !== 'MultipleChoiceQuestion') {
            return false;
        }

        return Str::lower($this->question['subtype']) == 'arq';
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
        return ! ($this->isMultipleChoiceQuestion() || $this->isInfoscreenQuestion() || $this->isArqQuestion());
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
        if ($this->obj && method_exists($this->obj, 'getTemplate')) {
            return view('livewire.teacher.questions.'.$this->obj->getTemplate())->layout('layouts.base');
        }

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

        if (is_array($value)) {
            $lastIndex = array_key_last($value);
            if(array_key_exists($lastIndex, $value)) {
                $tmpFileUpload = $value[array_key_last($value)];
                $this->sortOrderAttachments[] = $tmpFileUpload->getFileName();
            }
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
            if($this->obj && method_exists($this->obj, 'customValidation')){
                $this->obj->customValidation();
            }
            $this->checkTaxonomyValues();

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

    public function isSettingsGeneralPropertyVisible($property)
    {
        if($this->obj && property_exists($this->obj,'settingsGeneralPropertiesVisibility') && is_array($this->obj->settingsGeneralPropertiesVisibility)) {
            $this->settingsGeneralPropertiesVisibility = array_merge($this->settingsGeneralPropertiesVisibility, $this->obj->settingsGeneralPropertiesVisibility);
        }

        if(array_key_exists($property,$this->settingsGeneralPropertiesVisibility)){
            return (bool) $this->settingsGeneralPropertiesVisibility[$property];
        }

        return true;
    }

    public function isSettingsGeneralPropertyDisabled($property, $asText = false)
    {
        if($this->obj && method_exists($this->obj,'isSettingsGeneralPropertyDisabled')){
            return $this->obj->isSettingsGeneralPropertyDisabled($property, $asText);
        }

        if($this->obj && property_exists($this->obj,'settingsGeneralDisabledProperties') && is_array($this->obj->settingsGeneralDisabledProperties) && in_array($property,$this->obj->settingsGeneralDisabledProperties)){
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

        if(!$this->isCloneRequest) {
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
        }

        if ($this->isCloneRequest || $response->getStatusCode() ) {
            $this->attachments = collect($this->attachments)->reject(function ($attachment) use ($attachmentUuid) {
                return $attachment->uuid == $attachmentUuid;
            });
        }
        $this->attachmentsCount--;
    }

    public function removeUpload($tempFile)
    {
        $this->uploads = collect($this->uploads)->reject(function ($tempUpload) use ($tempFile) {
            return $tempUpload->getFileName() == $tempFile;
        })->toArray();
        $this->attachmentsCount--;
    }

    public function removeVideo($videoId)
    {
        $this->videos = collect($this->videos)->reject(function ($item) use ($videoId) {
            return $item['id'] == $videoId;
        })->toArray();
        $this->sortOrderAttachments = collect($this->sortOrderAttachments)->reject(function($item) use ($videoId){
            return $item == $videoId;
        })->toArray();

        $this->attachmentsCount--;
    }

    public function handleNewVideoAttachment($link)
    {
        if($this->validateVideoLink($link)) {
            $video =  ['id' => Uuid::uuid4()->toString(), 'link' =>$link];
            $this->videos[] = $video;
            $this->sortOrderAttachments[] = $video['id'];
            return $this->attachmentsCount++;
        }

        $this->dispatchBrowserEvent('video-url-not-supported', __('cms.Video URL not supported'));
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
                "type"  => "video",
                "link"  => $video['link'],
                "title" => $video['title']
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
        $method = 'remove'. Str::ucfirst($item);
        if(method_exists($this, $method)) {
            $this->$method($id);
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
        if ($this->obj && method_exists($this->obj, 'preparePropertyBag')) {
            $this->obj->preparePropertyBag();
        }

        $this->question['test_id'] = $activeTest->id;
        $this->question['is_open_source_content'] = $activeTest->is_open_source_content ?? 0;

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
            $this->question['maintain_position'] = $tq->maintain_position;
            $this->question['add_to_database'] = $q->add_to_database;
            $this->question['discuss'] = $tq->discuss;
            $this->question['decimal_score'] = $q->decimal_score;

            $this->educationLevelId = $q->education_level_id;
            $this->rttiToggle = filled($this->question['rtti']);
            $this->bloomToggle = filled($this->question['bloom']);
            $this->millerToggle = filled($this->question['miller']);
            $this->initWithTags = $q->tags;
            $this->initWithTags->each(function($tag) {
                $this->question['tags'][] = $tag->name;
            });

            $this->attachmentsCount = count($this->attachments);

            if ($this->isCompletionQuestion()) {
                $this->question['question'] = $this->decodeCompletionTags($q);
            }

            if ($this->obj && method_exists($this->obj, 'initializePropertyBag')) {
                $this->obj->initializePropertyBag($q);
            }
        }

        if ($this->obj && method_exists($this->obj, 'createAnswerStruct')) {
            $this->obj->createAnswerStruct();
        }
    }

    private function validateVideoLink($link)
    {
        return !! $this->getVideoHost($link);
    }

    private function getVideoHost($link)
    {
        $youtube = collect(['youtube.com', 'youtu.be']);
        $vimeo = collect(['vimeo.com']);
        $host = null;

        $youtube->each(function($opt) use ($link, &$host) {
            if (Str::contains($link, $opt)) {
                $host = 'youtube';
            }
        });

        $vimeo->each(function($opt) use ($link, &$host) {
            if (Str::contains($link, $opt)) {
                $host = 'vimeo';
            }
        });

        return $host;
    }

    private function checkTaxonomyValues()
    {
        $rulesToValidate = null;
        collect(['rtti', 'bloom', 'miller'])->each(function($taxonomy) use (&$rulesToValidate) {
            $toggle = $taxonomy.'Toggle';
            $warningShow = $taxonomy.'WarningShown';
            if ($this->$toggle && blank($this->question[$taxonomy]) && !$this->$warningShow) {
                $this->$warningShow = true;
                $rulesToValidate["question.$taxonomy"] = 'required';
            }
            if (!$this->$toggle && filled($this->question[$taxonomy])) {
                $this->question[$taxonomy] = '';
            }
        });

        if($rulesToValidate) {
            $this->validate($rulesToValidate);
        }
    }

    public function getUploadOrVideo($sortHash)
    {
        $video = $upload = null;

        $upload = collect($this->uploads)->first(function($upload) use ($sortHash){
            return $upload->getFileName() === $sortHash;
            return $upload->id == $sortHash;
        });

        $video = collect($this->videos)->first(function($video) use ($sortHash) {
            return $video['id'] = $sortHash;
            return $video == $sortHash;
        });

        return [$upload, $video];
    }

    public function setVideoTitle($videoUrl, $title)
    {
        $this->videos = collect($this->videos)->map(function($video) use ($title, $videoUrl) {
           if ($video['link'] == $videoUrl) {
                $video['title'] = $title;
           }
           return $video;
        })->toArray();
    }
}
