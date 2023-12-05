<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\WithFileUploads;
use Ramsey\Uuid\Uuid;
use tcCore\Attachment;
use tcCore\BaseSubject;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Controllers\GroupQuestionQuestions\AttachmentsController as GroupAttachmentsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Controllers\TestQuestions\AttachmentsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Controllers\TestsController;
use tcCore\Http\Enums\Taxonomy\Bloom;
use tcCore\Http\Enums\Taxonomy\Miller;
use tcCore\Http\Enums\UserFeatureSetting as UserFeatureSettingEnum;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Group;
use tcCore\Http\Livewire\Teacher\Cms\Providers\InfoScreen;
use tcCore\Http\Livewire\Teacher\Cms\Providers\MultipleChoice;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Open;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Ranking;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Relation;
use tcCore\Http\Livewire\Teacher\Cms\Providers\TrueFalse;
use tcCore\Http\Livewire\Teacher\Cms\Providers\TypeProvider;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Http\Requests\Request;
use tcCore\Http\Traits\WithQueryStringSyncing;
use tcCore\Http\Traits\WithRelationQuestionAttributes;
use tcCore\Lib\CkEditorComments\User;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Question;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\TestTake;
use tcCore\UserFeatureSetting;

class Constructor extends TCComponent implements QuestionCms
{
    use WithFileUploads;
    use WithQueryStringSyncing;
    use WithRelationQuestionAttributes;

    public $showSelectionOptionsModal = false;

    public $openTab = 1;

    public $testQuestionId;

    public $testId;

    public $type;

    public $subtype;

    public $owner;

    public $groupQuestionQuestionId;

    public $answerEditorId;

    public $questionEditorId;

    public $audioUploadOptions = [];

    public $audioAttachmentOptions = [];

    public $uploads = [];

    public $attachments = [];

    public $initWithTags = [];

    public $videos = [];

    public $isPartOfGroupQuestion = false;

    public $isCloneRequest = false;

    public $withDrawer = false;

    public $authors = '';

    public $pValues = [];

    public $referrer;

    public $attachmentsCount = 0;

    public $cmsPropertyBag = [];

    public $rttiToggle = false;
    public $bloomToggle = false;
    public $millerToggle = false;
    public $rttiWarningShown = false;
    public $bloomWarningShown = false;
    public $millerWarningShown = false;
    public $rttiOptions = [];
    public $bloomOptions = [];
    public $millerOptions = [];
    public $lang = 'nl_NL';
    private $lastSelectedLanguage;
    public $allowWsc = false;
    public $wscLanguages;
    public const SETTING_LANG = 'spellchecker language';

    public const ALLOWED_QUESTION_PROPERTIES_TO_UPDATE = ['question'];

    protected $tags = [];

    public $testIsPublished;

    public $currentUrl;

    protected $queryString = [
        'action', 'type', 'subtype', 'testId', 'testQuestionId', 'groupQuestionQuestionId', 'owner', 'isCloneRequest', 'withDrawer' => ['except' => false], 'referrer' => ['except' => false],
    ];

    protected $typesNeedIsolate = ['MatchingQuestion'];

    public $testName = 'test_name';

    public $subjectId;

    public $educationLevelId;

    public $action;

    public $questionId;

    public $questionIndex;

    public $question;

    /**
     * @var InfoScreen|MultipleChoice|Open|Ranking|TrueFalse|null
     */
    private $obj = '';

    public $sortOrderAttachments = [];

    public $dirty = false;

    public $withRedirect = true;
    public $emptyState = false;
    public $loading = false;
    public $showDirtyQuestionModal = false;
    public $nextQuestionToShow = [];
    public $forceOpenNewQuestion = false;
    public $uniqueQuestionKey = '';
    public $duplicateQuestion = false;
    public $canDeleteTest = false;

    public array $uploadRules = [];

    protected function rules()
    {
        $rules = ['question.question' => 'required'];
        if ($this->obj && method_exists($this->obj, 'mergeRules')) {
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

        $return = [
            'question.question' => __('cms.Vraagstelling'),
            'question.answer'   => __('cms.Antwoordmodel')
        ];

        if ($this->obj instanceof InfoScreen) {
            $return['question.question'] = __('cms.Informatietekst');
        }

        if ($this->obj instanceof Group) {
            $return['question.name'] = __('cms.naam vraaggroep');
        }

        return $return;
    }

    protected function getMessages(): array
    {
        return [
            'question.rtti.required'   => __('cms.rtti warning'),
            'question.bloom.required'  => __('cms.bloom warning'),
            'question.miller.required' => __('cms.miller warning'),
            'question.answers.*.score' => [
                'integer' => __('cms.half_point_validation_text'),
                'numeric' => __('cms.numeric_validation_text'),
            ],
            'question.answers.*.*'     => __('cms.De gemarkeerde velden zijn verplicht'),
            'question.score'           => __('cms.Er dient minimaal 1 punt toegekend te worden'),
            'question.answer_svg'      => __('cms.drawing-question-required-answer'),
        ];
    }

    private function resetQuestionProperties($activeTest)
    {
        $this->question = [
            'add_to_database'          => 1,
            'answer'                   => '',
            'bloom'                    => '',
            'closeable'                => 0,
            'decimal_score'            => 1,
            'discuss'                  => 1,
            'maintain_position'        => 0,
            'miller'                   => '',
            'is_open_source_content'   => 0,
            'tags'                     => [],
            'note_type'                => 'NONE',
            'order'                    => 0,
            'question'                 => '',
            'rtti'                     => '',
            'score'                    => 1,
            'subtype'                  => '',
            'type'                     => '',
            'attainments'              => [],
            'learning_goals'           => [],
            'test_id'                  => '',
            'all_or_nothing'           => false,
            'lang'                     => $this->lang,
            'add_to_database_disabled' => 0,
            'draft'                    => $activeTest->draft,
        ];
        $this->question = array_merge($this->question, $this->featureSettingDefaults());

        $this->audioUploadOptions = [];

        $this->uploads = [];

        $this->attachments = [];

        $this->initWithTags = [];

        $this->videos = [];

        $this->isPartOfGroupQuestion = false;

        $this->authors = '';

        $this->pValues = [];

        $this->attachmentsCount = 0;

        $this->cmsPropertyBag = [];

        $this->rttiToggle = false;
        $this->bloomToggle = false;
        $this->millerToggle = false;
        $this->rttiWarningShown = false;
        $this->bloomWarningShown = false;
        $this->millerWarningShown = false;

        $this->tags = [];
        $this->dirty = false;
        $this->registerDirty = true;
        $this->forceOpenNewQuestion = false;
        $this->uniqueQuestionKey = $this->testQuestionId . $this->groupQuestionQuestionId . $this->action . $this->questionEditorId;
        $this->duplicateQuestion = false;
        $this->canDeleteTest = false;
    }


    public function requiresAnswer()
    {
        if ($this->obj && property_exists($this->obj, 'requiresAnswer')) {
            return $this->obj->requiresAnswer;
        }
        return false;
    }

    protected $listeners = [
        'new-tags-for-question'                        => 'handleExternalUpdatedProperty',
        'updated-attainment'                           => 'handleExternalUpdatedProperty',
        'updated-learning-goal'                        => 'handleExternalUpdatedProperty',
        'new-video-attachment'                         => 'handleNewVideoAttachment',
        'drawing_data_updated'                         => 'handleUpdateDrawingData',
        'refresh'                                      => 'render',
        'showQuestion'                                 => 'showQuestion',
        'addQuestion'                                  => 'addQuestion',
        'showEmpty'                                    => 'showEmpty',
        'questionDeleted'                              => '$refresh',
        'addQuestionFromDirty'                         => 'addQuestionFromDirty',
        'testSettingsUpdated'                          => 'handleUpdatedTestSettings',
        'test-updated'                                 => 'testPublished',
        'relation-question-words-updated'              => 'newRelationQuestionWords',
        'relation-question-accepted-word-list-changes' => 'newRelationQuestionWords',
    ];


    public function handleUpdateDrawingData($data)
    {
        $this->obj->handleUpdateDrawingData($data);
        $this->dispatchBrowserEvent('viewbox-changed');
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
        if (!$this->emptyState) {
            $this->obj = TypeFactory::create($this);
        }
    }

    public function updatedLang($value): void
    {
        $this->question['lang'] = $value;
    }

    public function mount()
    {
        $this->currentUrl = url()->current();
        $activeTest = Test::whereUuid($this->testId)->with('testAuthors', 'testAuthors.user')->firstOrFail();
        Gate::authorize('isAuthorOfTest', [$activeTest]);
        $this->isChild = false;
        $this->setTaxonomyOptions();

        $this->initialize($activeTest);

        if (blank($this->type) && blank($this->subtype)) {
            return $this->emptyState = true;
        }

        $this->initializeContext($this->action, $this->type, $this->subtype, $activeTest);
        $this->obj = TypeFactory::create($this);
        $this->initializePropertyBag($activeTest);
    }

    private function initialize($activeTest)
    {
        $this->lang = $this->getDefaultWscLanguage($activeTest);
        $this->resetQuestionProperties($activeTest);
        $this->canDeleteTest = $activeTest->canDelete(Auth::user());
        $this->testIsPublished = $activeTest->isPublished();
        $this->testName = $activeTest->name;
        $this->subjectId = $activeTest->subject_id;
        $this->educationLevelId = $activeTest->education_level_id;
        $this->allowWsc = Auth::user()->schoolLocation->allow_wsc;
        $this->wscLanguages = WscLanguage::casesWithDescription();
        $this->setUploadRules();
    }

    public function __call($method, $arguments = null)
    {
        if ($this->obj && is_array($method) && method_exists($this->obj, 'arrayCallback')) {
            return $this->obj->arrayCallback($method);
        }

        if ($this->obj && method_exists($this->obj, $method)) {
            if (!is_null($arguments)) {
                return $this->obj->$method($arguments);
            }
            return $this->obj->$method();
        }

        $newName = '_' . $method;
        if (method_exists($this, $newName)) {
            return $this->$newName($arguments);
        }

        if (!method_exists(get_parent_class($this), $method) && !str_contains($method, 'hydrate')) {
            $errorMessage = sprintf('Method (%s) not found on parent, type is `%s` (%s) on file %s:%d', $method, $this->question['type'], $this->question['subtype'], __FILE__, __LINE__);
            Bugsnag::notifyException(new Exception($errorMessage));
        }
        return parent::__call($method, $arguments);
    }

    public function save($withRedirect = true)
    {
        if ($this->emptyState) return false;
        if ($this->obj && method_exists($this->obj, 'prepareForSave')) {
            $this->obj->prepareForSave();
        }

        $this->validateAndReturnErrorsToTabOne();

        if ($this->action == 'edit' && !$this->isCloneRequest) {
            $response = $this->updateQuestion();

            if ($this->isPartOfGroupQuestion()) {
                $content = json_decode($response->getContent());
                if ($content != null && property_exists($content, 'uuid')) {
                    $this->groupQuestionQuestionId = $content->uuid;
                }
            }
        } else {
            $this->question['order'] = 0;
            if ($this->isCloneRequest) {
                $this->prepareForClone();
            }
            $response = $this->saveNewQuestion();

            if ($this->withDrawer) {
                $this->setQueryStringProperties($response);
                $this->question['order'] = json_decode($response->getContent(), true)['order'];
            }
        }
        $this->dispatchBrowserEvent('notify', ['message' => __('cms.Wijzigingen opgeslagen')]);

        if ($response->getStatusCode() == 200) {
            $this->handleAttachments($response);

            if ($this->obj && method_exists($this->obj, 'performAfterSaveActions')) {
                $this->obj->performAfterSaveActions($response);
            }
        }

        if (!Auth::user()->schoolLocation->canUseCmsWithDrawer()) {
            $this->returnToTestOverview();
            return true;
        }

        if ($withRedirect) {
            $this->returnToTestOverview();
            return true;
        }

        $this->rebootComponent();
    }

    protected function prepareForClone()
    {
        $this->question['order'] = 0;
        if (count($this->attachments)) {
            $this->question['clone_attachments'] = $this->attachments->map(function ($attachment) {
                return $attachment->uuid;
            })->toArray();
        }
        $this->isCloneRequest = false;
    }

    public function updated($name, $value)
    {
        $method = 'updated' . Str::dotToPascal($name);
        if ($this->obj && method_exists($this->obj, $method)) {
            $this->obj->$method($value);
        }
        if ($this->obj && method_exists($this->obj, 'updated')) {
            $this->obj->updated($name, $value);
        }

        $this->handleDirtyState($name);
    }

    public function updating($name, $value)
    {
        $method = 'updating' . ucfirst($name);
        if ($this->obj && method_exists($this->obj, $method)) {
            $this->obj->$method($value);
        }
        if ($this->obj && method_exists($this->obj, 'updating')) {
            $this->obj->updating($name, $value);
        }
        if ($name == 'question.question' && clean($this->question['question']) == clean($value)) {
            $this->registerDirty = false;
        }
    }

    private function handleDirtyState($updatedProperty)
    {
        if ($updatedProperty != 'loading' && $this->registerDirty) {
            $this->dirty = true;
        }
        $this->registerDirty = true;
    }

    public function showStatistics()
    {
        if ($this->isCloneRequest) {
            return false;
        }

        $method = 'showStatistics';
        if ($this->obj && method_exists($this->obj, $method)) {
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


    public function hasAllOrNothing()
    {
        return $this->obj instanceof MultipleChoice;
    }

    public function showQuestionScore()
    {
        $method = 'showQuestionScore';
        if ($this->obj && method_exists($this->obj, $method)) {
            return $this->obj->$method();
        }
        return true;
    }

    private function saveNewQuestion()
    {
        Request::filter($this->question);

        if ($this->isPartOfGroupQuestion()) {
            $gqqm = GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId);
            $cgqqr = new CreateGroupQuestionQuestionRequest($this->question);

            return (new GroupQuestionQuestionsController())->store($gqqm, $cgqqr);
        }

        return (new TestQuestionsController())->store(new CreateTestQuestionRequest($this->question));
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
            return view('livewire.teacher.questions.' . $this->obj->getTemplate())->layout('layouts.cms');
        }
        if ($this->emptyState) {
            return view('livewire.teacher.questions.cms-layout')->layout('layouts.cms');
        }
        throw new Exception('No template found for this question type.');
    }

    public function handleExternalUpdatedProperty(array $incomingData)
    {
        $property = array_keys($incomingData)[0];
        if ($this->shouldUpdatePropertyFromExternalSource($incomingData, $property)) {
            $this->question[$property] = array_values($incomingData[$property]);
            $this->dirty = true;
        }
    }

    public function updatingUploads(&$value)
    {
        if (!is_array($value) && Str::contains($value, 'fake')) {
            $value = $this->uploads;
        }

        if (is_array($value)) {
            $lastIndex = array_key_last($value);
            if (array_key_exists($lastIndex, $value)) {
                $tmpFileUpload = $value[array_key_last($value)];
                $this->sortOrderAttachments[] = $tmpFileUpload->getFileName();
            }
        }
    }

    public function returnToTestOverview()
    {
        if ($this->referrer) {
            if ($this->referrer === 'teacher.tests') {
                return redirect()->to(route($this->referrer));
            }
            if ($this->referrer === 'teacher.test-detail') {
                return redirect()->to(route($this->referrer, $this->testId));
            }
            if ($this->referrer === 'cake.filemanagement') {
                $fileManagementUuid = Test::whereUuid($this->testId)->first()->fileManagement->uuid;
                return CakeRedirectHelper::redirectToCake('files.view_testupload', $fileManagementUuid);
            }
        }
        $url = sprintf("tests/view/%s", $this->testId);
        if ($this->isPartOfGroupQuestion() && !$this->withDrawer) {
            $url = sprintf(
                'questions/view_group/%s/%s',
                $this->testId,
                $this->testQuestionId
            );
        }
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    private function validateAndReturnErrorsToTabOne($useUnprepareForSave = true)
    {
        try {
            $this->validate();
            if ($this->obj && method_exists($this->obj, 'customValidation')) {
                $this->obj->customValidation();
            }
            $this->checkTaxonomyValues();

        } catch (ValidationException $e) {
            if ($useUnprepareForSave && $this->obj && method_exists($this->obj, 'unprepareForSave')) {
                $this->obj->unprepareForSave();
            }
            $this->dispatchBrowserEvent('opentab', 1);
            throw ($e);
        }
    }

    private function updateQuestion()
    {
        if (!$this->dirty) {
            return Response::make('not dirty', 304);
        }
        $request = new CmsRequest();
        $request->merge($this->question);
        $request->filterInput();

        $request = $this->handleDraftStatusOfTestForUpdate($request);

        if ($this->isPartOfGroupQuestion()) {
            $groupQuestionQuestion = GroupQuestionQuestion::whereUuid($this->groupQuestionQuestionId)->first();
            $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId);

            $response = (new GroupQuestionQuestionsController())->updateGeneric(
                $groupQuestionQuestionManager,
                $groupQuestionQuestion,
                $request
            );

        } else {
            $response = (new TestQuestionsController())->updateFromWithin(
                TestQuestion::whereUUID($this->testQuestionId)->first(),
                $request
            );
        }

        return $response;
    }

    public function isSettingsGeneralPropertyVisible($property): bool
    {
        if ($this->obj instanceof TypeProvider) {
            return $this->obj->isSettingVisible($property);
        }

        return true;
    }

    public function isSettingsGeneralPropertyDisabled($property, $asText = false)
    {
        if ($this->obj instanceof TypeProvider) {
            return $this->obj->isSettingDisabled($property);
        }

        return false;
    }

    public function handleAttachmentSettingChange($data, $attachmentUuid)
    {
        $attachment = $this->attachments->where('uuid', $attachmentUuid)->first();
        $questionAttachment = $attachment->questionAttachments->where('question_id', $this->questionId)->first();

        $currentJson = $this->audioAttachmentOptions[$attachmentUuid]
            ?? json_decode($questionAttachment->options, true);

        $updatedJson = array_merge($currentJson ?? [], $data);

        $this->audioAttachmentOptions[$attachmentUuid] = $updatedJson;
        $this->question['questionAttachmentOptions'][$attachment->getKey()] = $updatedJson;

        $this->dirty = true;
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

        if (!$this->isCloneRequest) {
            if ($this->isPartOfGroupQuestion()) {
                $response = (new GroupAttachmentsController())
                    ->destroy(
                        GroupQuestionQuestionManager::getInstanceWithUuid("{$this->testQuestionId}.{$this->groupQuestionQuestionId}"),
                        $attachment
                    );
            } else {
                $response = (new AttachmentsController())
                    ->destroy(
                        TestQuestion::whereUuid($this->testQuestionId)->first(),
                        $attachment
                    );
            }
        }

        if ($this->isCloneRequest || $response->getStatusCode()) {
            $this->attachments = $this->attachments->reject(function ($attachment) use ($attachmentUuid) {
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
        $this->sortOrderAttachments = collect($this->sortOrderAttachments)->reject(function ($item) use ($videoId) {
            return $item == $videoId;
        })->toArray();

        $this->attachmentsCount--;
    }

    public function handleNewVideoAttachment($link)
    {
        if ($this->validateVideoLink($link)) {
            $link = Attachment::convertYoutubeShortsLink($link);
            $video = ['id' => Uuid::uuid4()->toString(), 'link' => $link];
            $this->videos[] = $video;
            $this->sortOrderAttachments[] = $video['id'];
            $this->dirty = true;
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
            $this->createAttachmentWithRequest($attachementRequest, $response);
        });
    }

    private function handleVideoAttachments($response)
    {
        collect($this->videos)->each(function ($video) use ($response) {
            $attachmentRequest = new  CreateAttachmentRequest([
                "type"  => "video",
                "link"  => $video['link'],
                "title" => $video['title']
            ]);
            $this->createAttachmentWithRequest($attachmentRequest, $response);
        });
    }

    public function createAttachmentWithRequest(CreateAttachmentRequest $request, $response)
    {
        if ($this->isPartOfGroupQuestion()) {
            return (new GroupAttachmentsController())
                ->store(
                    GroupQuestionQuestionManager::getInstanceWithUuid("{$response->original->group_question_question_path}.{$response->original->uuid}"),
                    $request
                );
        }
        return (new AttachmentsController())
            ->store(
                TestQuestion::find($response->original->id),
                $request
            );
    }


    public function removeItem($item, $id)
    {
        $method = 'remove' . Str::ucfirst($item);
        if (method_exists($this, $method)) {
            $this->$method($id);
        }
        $this->dispatchBrowserEvent('attachments-updated');
    }

    private function removeQuestion()
    {
        if (!$this->editModeForExistingQuestion()) {
            if (Auth::user()->schoolLocation->canUseCmsWithDrawer()) {
                return $this->openLastQuestion();
            }
            return $this->returnToTestOverview();
        }

        if ($this->isPartOfGroupQuestion()) {
            $groupQuestionQuestion = GroupQuestionQuestion::whereUuid($this->groupQuestionQuestionId)->first();
            $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($this->testQuestionId);

            $response = (new GroupQuestionQuestionsController())->destroy(
                $groupQuestionQuestionManager,
                $groupQuestionQuestion
            );
        } else {
            $testQuestion = TestQuestion::whereUuid($this->testQuestionId)->firstOrFail();

            $response = (new TestQuestionsController())->destroy($testQuestion);
        }


        if ($response->getStatusCode() == 200) {
            if (Auth::user()->schoolLocation->canUseCmsWithDrawer()) {
                return $this->openLastQuestion();
            }
            return $this->returnToTestOverview();
        }
    }

    public function updatedUploads($value)
    {
        $this->attachmentsCount++;
        $this->dirty = true;
    }

    public function isPartOfGroupQuestion(): bool
    {
        return $this->isPartOfGroupQuestion;
    }

    private function setIsPartOfGroupQuestion()
    {
        $this->isPartOfGroupQuestion = ($this->owner == 'group');
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


        $this->withRedirect = !(Auth::user()->schoolLocation->canUseCmsWithDrawer() && $this->withDrawer);
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
            } else {
                $tq = TestQuestion::whereUuid($this->testQuestionId)->first();
            }
            $q = $tq->question;
            $this->attachments = $q->attachments()->with('questionAttachments')->get();

            $q = (new QuestionHelper())->getTotalQuestion($q);
            $this->pValues = $q->getQuestionInstance()->getRelation('pValue');

            $this->questionId = $q->getKey();
            $this->question['bloom'] = $q->bloom;
            $this->question['rtti'] = $q->rtti;
            $this->question['miller'] = $q->miller;
            $this->question['answer'] = $q->answer;
            $this->question['question'] = $q->getQuestionHTML();
            $this->question['score'] = $q->score;
            $this->question['note_type'] = $q->note_type;
            $this->question['attainments'] = $q->getQuestionAttainmentsAsArray();
            $this->question['learning_goals'] = $q->getQuestionLearningGoalsAsArray();
            $this->question['order'] = $tq->order;
            $this->question['all_or_nothing'] = $q->all_or_nothing;
            $this->question['closeable'] = $q->closeable;
            $this->question['maintain_position'] = $tq->maintain_position;
            $this->question['add_to_database'] = $q->add_to_database;
            $this->question['add_to_database_disabled'] = $q->add_to_database_disabled;
            $this->question['discuss'] = $tq->discuss;
            $this->question['decimal_score'] = $q->decimal_score;
            $this->question['lang'] = !is_null($q->lang) ? $q->lang : Auth::user()->schoolLocation->wscLanguage;
            $this->question['draft'] = $q->draft;
            $this->question['shuffle'] = $q->shuffle;

            $this->lang = $this->question['lang'];
            $this->educationLevelId = $q->education_level_id;
            $this->rttiToggle = filled($this->question['rtti']);
            $this->bloomToggle = filled($this->question['bloom']);
            $this->millerToggle = filled($this->question['miller']);
            $this->initWithTags = $q->tags;
            $this->initWithTags->each(function ($tag) {
                $this->question['tags'][] = $tag->name;
            });

            $this->attachmentsCount = count($this->attachments);

            if ($this->obj && method_exists($this->obj, 'initializePropertyBag')) {
                $this->obj->initializePropertyBag($q);
            }

            $this->duplicateQuestion = $activeTest->getDuplicateQuestionIds()->contains($this->questionId);
            $this->authors = $q->getAuthorNamesString();
        }

        if ($this->obj && method_exists($this->obj, 'createAnswerStruct')) {
            $this->obj->createAnswerStruct();
        }
    }

    private function validateVideoLink($link)
    {
        return !!$this->obj->getVideoHost($link);
    }

    private function checkTaxonomyValues()
    {
        $rulesToValidate = null;
        collect(['rtti', 'bloom', 'miller'])->each(function ($taxonomy) use (&$rulesToValidate) {
            $toggle = $taxonomy . 'Toggle';
            $warningShow = $taxonomy . 'WarningShown';
            if ($this->$toggle && blank($this->question[$taxonomy]) && !$this->$warningShow) {
                $this->$warningShow = true;
                $rulesToValidate["question.$taxonomy"] = 'required';
            }
            if (!$this->$toggle && filled($this->question[$taxonomy])) {
                $this->question[$taxonomy] = '';
            }
        });

        if ($rulesToValidate) {
            $this->validate($rulesToValidate);
        }
    }

    public function getUploadOrVideo($sortHash)
    {
        $video = $upload = null;

        $upload = collect($this->uploads)->first(function ($upload) use ($sortHash) {
            return $upload->getFileName() === $sortHash;
        });

        $video = collect($this->videos)->first(function ($video) use ($sortHash) {
            return $video['id'] == $sortHash;
        });

        return [$upload, $video];
    }

    public function setVideoTitle($videoUrl, $title)
    {
        $this->videos = collect($this->videos)->map(function ($video) use ($title, $videoUrl) {
            if ($video['link'] == $videoUrl) {
                $video['title'] = $title;
            }
            return $video;
        })->toArray();
    }

    public function setQuestionProperty($property, $value)
    {
        if(in_array($property,self::ALLOWED_QUESTION_PROPERTIES_TO_UPDATE)) {
            $this->question[$property] = $value;
        }
    }

    public function showQuestion($args)
    {
        if (!$this->forceOpenNewQuestion && $this->needsSavingBeforeShowingQuestion($args['shouldSave'])) {
            if (!$this->completedMandatoryFields()) {
                return $this->leavingDirtyQuestion($args);
            }
            $this->loading = true;
            $this->save(false);
        }
        $this->loading = true;
        $this->dispatchBrowserEvent('question-change', ['new' => $args['questionUuid'], 'old' => $this->testQuestionId]);

        $this->handleQueryStringForExistingQuestion($args);

        $this->rebootComponent();

        $this->refreshDrawer();
    }

    public function addQuestion($args)
    {
        if (!$this->forceOpenNewQuestion && $this->needsSavingBeforeAddingNewQuestion($args)) {
            $this->save(false);
        }

        $this->emitTo('drawer.cms', 'addQuestionResponse', $args);

        $this->handleQueryStringForCreatingNewQuestion($args);

        $this->rebootComponent();

        $this->refreshDrawer();

        $message = __('cms.item added', ['item' => $args['subtype'] === 'group' ? __('cms.group-question') : __('drawing-modal.Vraag')]);
        $this->dispatchBrowserEvent('notify', ['message' => $message]);
    }

    public function isGroupQuestion()
    {
        return !!($this->type === 'GroupQuestion');
    }

    public function needIsolate()
    {
        return !!($this->type && in_array($this->type, $this->typesNeedIsolate));
    }

    public function resolveOrderNumber()
    {
        if ($this->isGroupQuestion() || $this->emptyState) {
            return 1;
        }

        $questionList = Test::whereUuid($this->testId)->first()->getQuestionOrderList();

        if ($this->editModeForExistingQuestion()) {
            return $questionList[$this->questionId] ?? 1;
        }

        if ($this->owner === 'group') {
            $groupQuestionId = TestQuestion::whereUuid($this->testQuestionId)->value('question_id');
            $lastQuestionIdInGroup = GroupQuestionQuestion::where('group_question_id', $groupQuestionId)
                ->orderBy('order', 'desc')->value('question_id');
            return isset($questionList[$lastQuestionIdInGroup]) ? $questionList[$lastQuestionIdInGroup] + 1 : 1;
        }
        return count($questionList) + 1;
    }

    public function saveAndRefreshDrawer()
    {
        $this->save($this->withRedirect);

        $this->refreshDrawer();
    }

    private function setQueryStringProperties($response)
    {
        $this->action = 'edit';

        if ($testQuestion = TestQuestion::whereUuid(json_decode($response->getContent())->uuid)->first()) {
            $this->testQuestionId = $testQuestion->uuid;
            $this->groupQuestionQuestionId = '';
        } else {
            $this->groupQuestionQuestionId = json_decode($response->getContent())->uuid;
        }

        /**
         * Update the CKEditor ids to trigger reinit of the editor after DOM change
         */
        $this->answerEditorId = Str::uuid()->__toString();
        $this->questionEditorId = Str::uuid()->__toString();
    }

    private function resetToEmptyState()
    {
        $this->emptyState = true;
        $this->reset(['type', 'subtype', 'testQuestionId']);
        $this->emitTo('drawer.cms', 'show-empty');
    }

    private function openLastQuestion()
    {
        $testQuestionUuid = Test::whereUuid($this->testId)
            ->first()
            ->testQuestions()
            ->latest()
            ->value('uuid');

        if (!$testQuestionUuid) {
            $this->resetToEmptyState();
            return true;
        }

        $params = [
            'testQuestionUuid' => $testQuestionUuid,
            'questionUuid'     => null,
            'isSubQuestion'    => false,
            'shouldSave'       => false,
        ];

        $this->showQuestion($params);
        $this->refreshDrawer();
    }

    private function isDirty()
    {
        return $this->dirty;
    }

    private function refreshDrawer()
    {
        $this->emitTo('drawer.cms', 'refreshDrawer', [
            'testQuestionId'          => $this->testQuestionId,
            'action'                  => $this->action,
            'owner'                   => $this->owner,
            'testId'                  => $this->testId,
            'groupQuestionQuestionId' => $this->groupQuestionQuestionId,
            'type'                    => $this->type,
            'subtype'                 => $this->subtype,
            'isCloneRequest'          => $this->isCloneRequest,
        ]);
    }

    public function getAmountOfQuestionsProperty()
    {
        return Test::whereUuid($this->testId)->first()?->getAmountOfQuestions();
    }

    private function testHasNoQuestions()
    {
        $questionAmount = $this->getAmountOfQuestionsProperty();
        return !$questionAmount || !!($questionAmount['regular'] === 0 && $questionAmount['group'] === 0);
    }

    private function testHasQuestions()
    {
        return !$this->testHasNoQuestions();
    }

    public function saveAndRedirect()
    {
        if ($this->isDirty()) {
            if ($this->completedMandatoryFields()) {
                return $this->save();
            }
            return $this->dispatchBrowserEvent('show-dirty-question-modal', ['leavingTest' => true]);
        }
        return $this->returnToTestOverview();
    }

    /**
     * @param $args
     * @return void
     */
    private function handleQueryStringForExistingQuestion($args): void
    {
        $this->action = 'edit';
        $this->isCloneRequest = false;
        $this->emptyState = false;
        $testQuestion = TestQuestion::whereUuid($args['testQuestionUuid'])->with('question')->first();
        if ($args['isSubQuestion']) {
            $groupQuestion = $testQuestion->question;
            $question = Question::whereUuid($args['questionUuid'])->first();
            try {
                $this->groupQuestionQuestionId = $groupQuestion->groupQuestionQuestions()->firstWhere('question_id', $question->getKey())->uuid;
            } catch(Exception $e){
                // the groupQuestionQuestion could not be found and has probably to do with using the back button
                // we therefor lead the user to the test page again with a notification
                $this->resetToEmptyState();
                $this->dispatchBrowserEvent('notify', ['message' => __('cms.Sorry, er ging iets fout, probeer het nogmaals'), 'type' => 'error']);
                return;
            }
            $this->type = $question->type;
            $this->subtype = $question->subtype;
            $this->owner = 'group';
            $this->testQuestionId = $args['testQuestionUuid'];
        } else {
            $this->type = $testQuestion->question->type;
            $this->subtype = $testQuestion->question->subtype;
            $this->owner = 'test';
            $this->groupQuestionQuestionId = '';
            $this->testQuestionId = $args['testQuestionUuid'];
        }
    }

    /**
     * @param $args
     * @return void
     */
    private function handleQueryStringForCreatingNewQuestion($args): void
    {
        $this->action = 'add';
        $this->type = $args['type'];
        $this->subtype = $args['subtype'];
        $this->groupQuestionQuestionId = '';
        $this->emptyState = false;
        if (filled($args['groupId'])) {
            $this->owner = 'group';
            $this->testQuestionId = $args['groupId'];
        } else {
            $this->owner = 'test';
            $this->testQuestionId = '';
        }
    }

    private function rebootComponent(): void
    {
        $this->mount();
        $this->resetErrorBag();
        $this->render();
    }

    public function leavingDirtyQuestion($args = [])
    {
        $this->dispatchBrowserEvent('show-dirty-question-modal', ['goingToExisting' => true]);
        $this->nextQuestionToShow = $args;
    }

    public function continueToNextQuestion()
    {
        $this->nextQuestionToShow['shouldSave'] = false;

        if (Arr::exists($this->nextQuestionToShow, 'type')) {
            return $this->addQuestion($this->nextQuestionToShow);
        }
        return $this->showQuestion($this->nextQuestionToShow);
    }

    public function showEmpty()
    {
        $this->type = '';
        $this->subtype = '';
        $this->action = 'add';
        $this->emptyState = true;
        $this->dispatchBrowserEvent('show-empty');
    }

    private function completedMandatoryFields()
    {
        if ($this->obj && method_exists($this->obj, 'passesCustomMandatoryRules')) {
            return !!$this->obj->passesCustomMandatoryRules();
        }

        return !Validator::make((array)$this, $this->getRules())->fails();
    }

    /**
     * @param $args
     * @return bool
     */
    private function needsSavingBeforeAddingNewQuestion($args): bool
    {
        return $this->isDirty() && !$this->emptyState && (Arr::exists($args, 'shouldSave') && $args['shouldSave']);
    }

    /**
     * @param $shouldSave
     * @return bool
     */
    private function needsSavingBeforeShowingQuestion($shouldSave): bool
    {
        return $shouldSave && $this->isDirty();
    }

    /**
     * @throws Exception
     */
    public function validateFromDirtyModal()
    {
        $this->validateAndReturnErrorsToTabOne(false);
    }

    public function addQuestionFromDirty($data)
    {
        if (!$this->completedMandatoryFields()) {
            $this->dispatchBrowserEvent('show-dirty-question-modal', ['goingToExisting' => false, 'group' => $data['group'], 'data' => $data]);
            return;
        }

        $this->save(false);

        $continueEvent = $data['group'] ? 'continue-to-add-group' : 'continue-to-new-slide';
        $this->dispatchBrowserEvent($continueEvent, $data);

        if ($data['newSubQuestion']) {
            $this->emit('newGroupId', $this->testQuestionId);
        }
    }

    public function getRulesForProvider()
    {
        return $this->getRules();
    }

    /**
     * @param array $incomingData
     * @param $property
     * @return bool
     */
    private function shouldUpdatePropertyFromExternalSource(array $incomingData, $property): bool
    {
        if (empty($incomingData[$property])) {
            return false;
        }
        if ($property === 'tags') {
            $values = array_values($incomingData[$property]);
            $existingValues = array_values($this->question[$property]);
            sort($values);
            sort($existingValues);
            if ($values === $existingValues) {
                return false;
            }
        }
        return true;
    }

    public function handleUpdatedTestSettings($settings)
    {
        $this->testName = $settings['name'] ?? $this->testName;
    }

    public function getPdfUrl()
    {
        $controller = new TemporaryLoginController();
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'options' => [
                'page'        => sprintf('/tests/view/%s', $this->testId),
                'page_action' => sprintf("Loading.show();Popup.load('/tests/pdf_showPDFAttachment/%s', 1000);", $this->testId),
            ]
        ]);

        return $controller->toCakeUrl($request);
    }

    private function removeTest($uuid)
    {
        $test = Test::whereUuid($uuid)->firstOrFail();

        if ($test->canDelete(Auth::user())) {
            $response = (new TestsController())->destroy($test);

            if ($response->getStatusCode() === 200) {
                return $this->returnToTestsList();
            }
        }

        $this->dispatchBrowserEvent('notify', ['message' => __('auth.something_went_wrong'), 'error']);
    }

    private function returnToTestsList()
    {
        if ($this->referrer) {
            if (in_array($this->referrer, ['teacher.tests', 'teacher.test-detail'])) {
                return redirect()->to(route('teacher.tests'));
            }
        }

        $options = TemporaryLogin::buildValidOptionObject('page', 'tests/index');
        return Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    public function saveIfDirty()
    {
        if ($this->isDirty()) {
            $this->save(false);
        }
    }

    public function clearQuestionBag()
    {
        if ($this->obj && method_exists($this->obj, 'clearQuestionBag')) {
            $this->obj->clearQuestionBag();
        }
        return true;
    }

    private function setTaxonomyOptions()
    {
        $this->rttiOptions = ['R', 'T1', 'T2', 'I'];

        $this->bloomOptions = Bloom::translations();

        $this->millerOptions = Miller::translations();
    }

    private function handleDraftStatusOfTestForUpdate(CmsRequest $request): CmsRequest
    {
        $request->merge(['test_draft' => Test::whereUuid($this->testId)->value('draft')]);
        return $request;
    }

    public function testPublished()
    {
        $this->testIsPublished = Test::whereUuid($this->testId)->first()->isPublished();
    }

    public function toPlannedTest($takeUuid)
    {
        $testTake = TestTake::whereUuid($takeUuid)->first();
        return auth()->user()->redirectToCakeWithTemporaryLogin($testTake->getPlannedTestOptions());
    }

    private function getDefaultWscLanguage($test)
    {
        if (UserFeatureSetting::getSetting(Auth::user(), UserFeatureSettingEnum::WSC_COPY_SUBJECT_LANGUAGE, default: true)) {
            if ($subjectLanguage = $test->getBaseSubjectLanguage()) {
                return $subjectLanguage;
            }
        }

        if ($language = UserFeatureSetting::getSetting(Auth::user(), UserFeatureSettingEnum::WSC_DEFAULT_LANGUAGE)) {
            return $language;
        }

        return WscLanguage::DUTCH;
    }

    private function featureSettingDefaults(): array
    {
        $featureSettings = UserFeatureSettingEnum::initialValues()->merge(UserFeatureSetting::getAll(Auth::user()));
        return [
            'add_to_database'   => $featureSettings[UserFeatureSettingEnum::QUESTION_PUBLICLY_AVAILABLE->value],
            'score'             => $featureSettings[UserFeatureSettingEnum::QUESTION_DEFAULT_POINTS->value],
            'decimal_score'     => $featureSettings[UserFeatureSettingEnum::QUESTION_HALF_POINTS_POSSIBLE->value],
            'auto_check_answer' => $featureSettings[UserFeatureSettingEnum::QUESTION_AUTO_SCORE_COMPLETION->value],
        ];
    }

    public function hasScoringDisabled(): bool
    {
        if ($this->obj instanceof TypeProvider) {
            return $this->obj->hasScoringDisabled();
        }
        return false;
    }

    public function questionSectionTitle(): string
    {
        if ($this->obj instanceof TypeProvider) {
            return $this->obj->questionSectionTitle();
        }
        return __('cms.Vraagstelling');
    }
    public function answerSectionTitle(): string
    {
        if ($this->obj instanceof TypeProvider) {
            return $this->obj->answerSectionTitle();
        }
        return __('cms.Antwoordmodel');
    }

    public function newRelationQuestionWords(array $data, bool $save = false): void
    {
        if ($this->obj instanceof Relation) {
            $this->obj->newRelationQuestionWords($data, $save);
        }
    }

    private function setUploadRules(): void
    {
        $mimeArray = ['pdf','mp3','png','jpeg','jpg'];

        $this->uploadRules = [
            'extensions' => ['data' => $mimeArray, 'message' => __('upload.upload_rule_extension')],
            'size'       => ['data' => 64000000, 'message' => __('upload.upload_rule_size')]
        ];
    }

}
