<?php namespace tcCore;

use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Lib\Question\Factory;
use tcCore\Traits\UuidTrait;

class GroupQuestionQuestion extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'group_question_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['group_question_id', 'question_id', 'order', 'maintain_position', 'discuss'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $callbacks = true;

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function (GroupQuestionQuestion $groupQuestionQuestion) {
            if ($groupQuestionQuestion->doCallbacks() && ($groupQuestionQuestion->getOriginal('order') != $groupQuestionQuestion->getAttribute('order') || $groupQuestionQuestion->getOriginal('group_question_id') != $groupQuestionQuestion->getAttribute('group_question_id'))) {
                $groupQuestionQuestion->groupQuestion->reorder($groupQuestionQuestion);
            }
        });

        $metadataCallback = function (GroupQuestionQuestion $groupQuestionQuestion) {
            if ($groupQuestionQuestion->doCallbacks()) {
                $tests = $groupQuestionQuestion->groupQuestion->gatherAffectedTests();

                foreach ($tests as $test) {
                    $test->performMetadata();
                }
            }
        };
        static::created($metadataCallback);

        static::restored($metadataCallback);

        static::deleted($metadataCallback);
    }

    public function setCallbacks($callbacks)
    {
        $this->callbacks = ($callbacks === true);
    }

    public function doCallbacks()
    {
        return $this->callbacks;
    }

    public function groupQuestion()
    {
        return $this->belongsTo('tcCore\Question', 'group_question_id');
    }

    public function question()
    {
        return $this->belongsTo('tcCore\Question', 'question_id');
    }

    public function duplicate($parent, array $attributes = [], $callbacks = true)
    {
        $groupQuestionQuestion = $this->replicate();
        $groupQuestionQuestion->fill($attributes);

        $groupQuestionQuestion->setAttribute('uuid', Uuid::uuid4());

        if ($callbacks === false) {
            $groupQuestionQuestion->setCallbacks(false);
        }

        $parent->groupQuestionQuestions()->save($groupQuestionQuestion);

        if ($callbacks === false) {
            $groupQuestionQuestion->setCallbacks(true);
        }

        return $groupQuestionQuestion;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'group_question_id':
                    if (is_array($value)) {
                        $query->whereIn('group_question_id', $value);
                    } else {
                        $query->where('group_question_id', '=', $value);
                    }
                    break;
                case 'question_id':
                    if (is_array($value)) {
                        $query->whereIn('question_id', $value);
                    } else {
                        $query->where('question_id', '=', $value);
                    }
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'order':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch (strtolower($key)) {
                case 'id':
                case 'order':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public static function store(GroupQuestionQuestionManager|TestQuestion $groupQuestionQuestionManager, array $questionProperties): GroupQuestionQuestion
    {
        if ($groupQuestionQuestionManager instanceof TestQuestion) {
            $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($groupQuestionQuestionManager->uuid);
        }
        $groupQuestion = $groupQuestionQuestionManager->getQuestionLink()->question;

        $question = Factory::makeQuestion($questionProperties['type']);
        if (!$question) {
            throw new QuestionException('Failed to create question with factory', 500);
        }

        $groupQuestionQuestion = new GroupQuestionQuestion();
        $groupQuestionQuestion->fill($questionProperties);
        $groupQuestionQuestion->setAttribute('group_question_id', $groupQuestion->getKey());

        $qHelper = new QuestionHelper();
        $questionData = [];
        if ($questionProperties['type'] == 'CompletionQuestion') {
            $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion(
                question: $questionProperties['question'],
                markAllAnswersAsCorrect: $questionProperties['subtype'] == 'completion'
            );
        }
        $totalData = array_merge($questionProperties, $questionData);
        $question->fill($totalData);

        Question::setAttributesFromParentModel($question, $groupQuestion);

        $question->getQuestionInstance()->setAttribute('is_subquestion', 1);

        if (!$question->save()) {
            throw new QuestionException('Failed to save question for group question', 500);
        }

        $groupQuestionQuestion->setAttribute('question_id', $question->getKey());

        if (!$groupQuestionQuestion->save()) {
            throw new QuestionException('Failed to create group question question', 500);
        }

        if (Question::usesDeleteAndAddAnswersMethods($questionProperties['type'])) {
            $groupQuestionQuestion->question->addAnswers($groupQuestionQuestion, $totalData['answers']);
        }

        $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());

        return $groupQuestionQuestion;
    }
}