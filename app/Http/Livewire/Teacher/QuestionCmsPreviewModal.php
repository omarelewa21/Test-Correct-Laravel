<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Str;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Http\Livewire\Teacher\Cms\Providers\MultipleChoice;
use tcCore\Http\Livewire\Teacher\Cms\TypeFactory;
use tcCore\Http\Traits\WithRelationQuestionAttributes;
use tcCore\Question;

class QuestionCmsPreviewModal extends TCModalComponent implements QuestionCms
{
    use WithRelationQuestionAttributes;

    public array $question = [
        'add_to_database'        => 1,
        'answer'                 => '',
        'bloom'                  => '',
        'closeable'              => 0,
        'decimal_score'          => 1,
        'discuss'                => 1,
        'maintain_position'      => 0,
        'miller'                 => '',
        'is_open_source_content' => 0,
        'tags'                   => [],
        'note_type'              => 'NONE',
        'order'                  => 0,
        'question'               => '',
        'rtti'                   => '',
        'score'                  => 1,
        'subtype'                => '',
        'type'                   => '',
        'attainments'            => [],
        'learning_goals'         => [],
        'test_id'                => '',
        'all_or_nothing'         => false,
        'uuid'                   => ''
    ];
    public string $action = 'edit';
    public array $cmsPropertyBag = [];
    public string $questionType;
    public int $attachmentsCount;
    public string $answerEditorId;
    public string $questionEditorId;
    public bool $isPartOfGroupQuestion = false;
    public bool $isPreview = true;
    public string $uniqueQuestionKey;
    public array $sortOrderAttachments = [];
    public int $subjectId;
    public int $educationLevelId;
    public int $questionId;
    public string $questionTitle;
    public bool $isCito = false;

    public bool $showSelectionOptionsModal = false;

    protected $questionModel;
    private $obj;
    public $pValues;
    public $initWithTags;
    public $attachments;
    public $authors;
    public $inTest = false;
    public $lang = 'nl_NL';
    public $allowWsc = false;

    protected static array $maxWidths = [
        'full' => 'modal-full-screen',
    ];

    /*
     *  Component initialization
     */
    public function mount($uuid, $inTest = false)
    {
        $question = Question::whereUuid($uuid)->first();
        $this->inTest = $inTest;
//        $this->allowWsc = Auth::user()->schoolLocation->allow_wsc;

        $this->initializeComponent($question);
    }

    public function booted()
    {
        $this->obj = TypeFactory::create($this);
        $this->questionModel = Question::whereUuid($this->question['uuid'])->first();
    }


    public function render()
    {
        if ($this->obj && method_exists($this->obj, 'getTemplate')) {
            return view('livewire.teacher.questions.' . $this->obj->getTemplate(), ['preview' => 'livewire.teacher.question-cms-preview-modal']);
        }

        throw new \Exception('Template not found for the question preview.');
    }

    public function __call($method, $arguments = null)
    {
        if (in_array($method, ['updating', 'updated'])) {
            return true;
        }
        if ($this->obj && is_array($method) && method_exists($this->obj, 'arrayCallback')) {
            return $this->obj->arrayCallback($method);
        }

        if ($this->obj && method_exists($this->obj, $method)) {
            if ($arguments) {
                return $this->obj->$method($arguments);
            }
            return $this->obj->$method();
        }

        $newName = '_' . $method;
        if (method_exists($this, $newName)) {
            return $this->$newName($arguments);
        }

        return parent::__call($method, $arguments);
    }

    /*
     * Modal settings
     */
    public static function modalMaxWidth(): string
    {
        return 'full';
    }

    public static function destroyOnClose(): bool
    {
        return BaseHelper::notProduction();
    }

    /*
     * Helper methods
     */
    private function initializeComponent(Question $question)
    {
        $this->question['type'] = $question->type;
        $this->question['uuid'] = $question->uuid;
        if (isset($question->subtype)) {
            $this->question['subtype'] = Str::lower($question->subtype);
        }

        $this->answerEditorId = Str::uuid()->__toString();
        $this->questionEditorId = Str::uuid()->__toString();
        $this->uniqueQuestionKey = $question->uuid;
        $this->isCito = $question->isCitoQuestion();

        $this->obj = TypeFactory::create($this);

        if ($this->obj && method_exists($this->obj, 'preparePropertyBag')) {
            $this->obj->preparePropertyBag();
        }

        logger(__METHOD__);
        logger($question::class, [$question]);
        $question = (new QuestionHelper())->getTotalQuestion($question->question);
        logger(__METHOD__);
        $this->pValues = $question->getQuestionInstance()->getRelation('pValue');

        $this->questionId = $question->question->getKey();
        $this->question['bloom'] = $question->bloom;
        $this->question['rtti'] = $question->rtti;
        $this->question['miller'] = $question->miller;
        $this->question['answer'] = $question->answer;
        $this->question['question'] = $question->question->getQuestionHTML();
        $this->question['score'] = $question->score;
        $this->question['note_type'] = $question->note_type;
        $this->question['attainments'] = $question->getQuestionAttainmentsAsArray();
        $this->question['learning_goals'] = $question->getQuestionLearningGoalsAsArray();
        $this->question['all_or_nothing'] = $question->all_or_nothing;
        $this->question['closeable'] = $question->closeable;
        $this->question['add_to_database'] = $question->add_to_database;
        $this->question['decimal_score'] = $question->decimal_score;
        $this->question['lang'] = $this->lang = $question->lang ?? 'nl_NL';

        $this->initWithTags = $question->tags;
        $this->initWithTags->each(function ($tag) {
            $this->question['tags'][] = $tag->name;
        });

        $this->attachments = $question->attachments;
        $this->attachmentsCount = count($this->attachments);

        $this->subjectId = $question->subject_id;
        $this->educationLevelId = $question->education_level_id;

        $this->questionTitle = $question->isType('GroupQuestion') ? html_entity_decode($question->name) : $question->title;
        $this->questionType = $question->isType('GroupQuestion') ? __('question.Vraaggroep') : $question->typeName;

        $this->authors = $question->getAuthorNamesString();

        if ($this->obj && method_exists($this->obj, 'initializePropertyBag')) {
            $this->obj->initializePropertyBag($question);
        }

        if ($this->obj && method_exists($this->obj, 'createAnswerStruct')) {
            $this->obj->createAnswerStruct();
        }
    }

    public function isGroupQuestion()
    {
        return $this->question['type'] === 'GroupQuestion';
    }

    public function isPartOfGroupQuestion()
    {
        return false;
    }

    public function requiresAnswer()
    {
        if ($this->obj && property_exists($this->obj, 'requiresAnswer')) {
            return $this->obj->requiresAnswer;
        }
        return false;
    }

    public function hasAllOrNothing()
    {
        return $this->obj instanceof MultipleChoice;
    }

    public function _showQuestionScore()
    {
        return true;
    }

    public function _showStatistics()
    {
        return true;
    }

    public function _showSettingsTaxonomy()
    {
        return true;
    }

    public function _showSettingsAttainments()
    {
        return true;
    }

    public function _showSettingsTags()
    {
        return true;
    }

    /*
     * Modal actions
     */
    public function addQuestion()
    {
        //if subquestion, and groupQuestion->question not empty or attachments
        if($this->questionModel->is_subquestion) {
            $groupQuestion = GroupQuestionQuestion::where('question_id', $this->questionModel->id)->first()->groupQuestion;
            if (!empty($groupQuestion->getQuestionInstance()->question) || $this->attachmentsCount > 0) {
                $this->emit('openModal', 'teacher.add-sub-question-confirmation-modal', ['questionUuid' => $this->questionModel->uuid]);
                return;
            }
        }

        $this->emitTo(QuestionBank::class, 'addQuestionFromDetail', $this->questionModel->uuid);
        $this->forceClose()->closeModal();
    }

    public function setVideoTitle($videoUrl, $title)
    {
    }
}