<?php namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Http\Controllers\QuestionsController;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Traits\WithQuestionFilteredHelpers;
use tcCore\Lib\Models\MtiBaseModel;
use tcCore\Scopes\QuestionAttainmentScope;
use tcCore\Services\QuestionHtmlConverter;
use tcCore\Traits\ExamSchoolQuestionTrait;
use tcCore\Traits\UserContentAccessTrait;
use tcCore\Traits\UserPublishing;
use tcCore\Traits\UuidTrait;

class Question extends MtiBaseModel
{
    use SoftDeletes;
    use UuidTrait;
    use ExamSchoolQuestionTrait;
    use UserContentAccessTrait;
    use WithQuestionFilteredHelpers;
    use UserPublishing;

    protected $casts = [
        'uuid'                     => EfficientUuid::class,
        'all_or_nothing'           => 'boolean',
        'add_to_database_disabled' => 'boolean',
        'draft'                    => 'boolean',
        'lang'                     => WscLanguage::class,
        'deleted_at'               => 'datetime',
    ];

    public $mtiBaseClass = 'tcCore\Question';
    public $mtiClassField = 'type';
    public $mtiParentTable = 'questions';

    const TYPE_OPEN = 'OPEN';
    const TYPE_CLOSED = 'CLOSED';

    const INLINE_IMAGE_PATTERN = '/custom/imageload.php?filename=';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subject_id',
        'education_level_id',
        'type',
        'question',
        'education_level_year',
        'score',
        'decimal_score',
        'note_type',
        'rtti',
        'bloom',
        'miller',
        'add_to_database',
        'is_open_source_content',
        'metadata',
        'external_id',
        'scope',
        'styling',
        'closeable',
        'html_specialchars_encoded',
        'is_subquestion',
        'all_or_nothing',
        'fix_order',
        'owner_id',
        'lang',
        'draft',
        'add_to_database_disabled',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $authors = null;

    /**
     * @var array or null
     */
    protected $attainments = null;

    protected $learning_goals = null;

    protected $tags = null;

    protected $onlyAddToDatabaseFieldIsModified = false;

    protected $duplicateQuestionKey = false;

    protected $groupQuestionPivot = false;

    public static function usesDeleteAndAddAnswersMethods($questionType)
    {
        return collect(['completionquestion', 'matchingquestion', 'rankingquestion', 'matrixquestion', 'multiplechoicequestion'])->contains(strtolower($questionType));
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (get_class($this) === 'tcCore\Question') {
            if (array_key_exists('authors', $attributes)) {
                $this->authors = $attributes['authors'];
            } elseif (array_key_exists('add_author', $attributes) || array_key_exists('delete_author', $attributes)) {
                $this->authors = $this->questionAuthors()->pluck('user_id')->all();
                if (array_key_exists('add_author', $attributes)) {
                    array_push($this->authors, $attributes['add_author']);
                }

                if (array_key_exists('delete_author', $attributes)) {
                    if (($key = array_search($attributes['delete_author'], $this->authors)) !== false) {
                        unset($this->authors[$key]);
                    }
                }
            }

            if (array_key_exists('attainments', $attributes)) {
                if ($attributes['attainments'] == '') {
                    $attributes['attainments'] = [];
                }

                //TC-106
                //convert attainments to an array if it is not an array
                //because it is expected to be an array
                if (!is_array($attributes['attainments'])) {
                    $attributes['attainments'] = [$attributes['attainments']];
                }

                foreach ($attributes['attainments'] as $key => $value) {
                    if (Uuid::isValid($value)) {
                        $attributes['attainments'][$key] = Attainment::whereUuid($value)->first()->getKey();
                    }
                }

                $this->attainments = $attributes['attainments'];
            } elseif (array_key_exists('add_attainment', $attributes) || array_key_exists('delete_attainment', $attributes)) {
                $this->attainment = $this->questionAttainments()->pluck('attainment_id')->all();
                if (array_key_exists('add_attainment', $attributes)) {
                    array_push($this->attainments, $attributes['add_attainment']);
                }

                if (array_key_exists('delete_attainment', $attributes)) {
                    if (($key = array_search($attributes['delete_attainment'], $this->attainments)) !== false) {
                        unset($this->attainments[$key]);
                    }
                }
            }

            if (array_key_exists('learning_goals', $attributes)) {
                if ($attributes['learning_goals'] == '') {
                    $attributes['learning_goals'] = [];
                }

                //TC-106
                //convert learning_goals to an array if it is not an array
                //because it is expected to be an array
                if (!is_array($attributes['learning_goals'])) {
                    $attributes['learning_goals'] = [$attributes['learning_goals']];
                }

                foreach ($attributes['learning_goals'] as $key => $value) {
                    if (Uuid::isValid($value)) {
                        $attributes['learning_goals'][$key] = LearningGoal::whereUuid($value)->first()->getKey();
                    }
                }

                $this->learning_goals = $attributes['learning_goals'];
            }

            if (array_key_exists('tags', $attributes)) {
                if ($attributes['tags'] == '') {
                    $attributes['tags'] = [];
                }

                $this->tags = Tag::findOrCreateByName($attributes['tags']);
            } elseif (array_key_exists('add_tag', $attributes) || array_key_exists('delete_tag', $attributes)) {
                $this->tags = $this->tagRelations()->pluck('tag_id')->all();
                if (array_key_exists('add_tag', $attributes)) {
                    $tagId = Tag::where('name', $attributes['add_tag'])->value('id');
                    if ($tagId) {
                        array_push($this->tags, $attributes['add_tag']);
                    }
                }

                if (array_key_exists('delete_tag', $attributes)) {
                    $tagId = Tag::where('name', $attributes['delete_tag'])->value('id');
                    if ($tagId) {
                        if (($key = array_search($attributes['delete_tag'], $this->tags)) !== false) {
                            unset($this->tags[$key]);
                        }
                    }
                }
            }
        } else {
            $question = $this->getQuestionInstance();
            $this->authors = $question->authors;
            $this->attainments = $question->attainments;
            $this->tags = $question->tags;
        }
    }

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::creating(function (Question $question) {
            self::addOwnerId($question->getQuestionInstance());
        });
        static::created(function (Question $question) {
            QuestionAuthor::addAuthorToQuestion($question);
        });
        static::saving(function (Question $question) {
            $question->handleExamPublishingQuestion();
        });

        static::saved(function (Question $question) {
            if (get_class($question) === 'tcCore\Question') {
                if ($question->authors !== null) {
                    $question->saveAuthors();
                }

                if ($question->attainments !== null) {
                    $question->saveAttainments();
                }

                if ($question->learning_goals !== null) {
                    $question->saveLearningGoals();
                }

                if ($question->tags !== null) {
                    $question->saveTags();
                }
            }
        });
    }

    public function tags()
    {
        // Tags are attached to questions, so ALWAYS get this relation from the actual question
        $question = $this->getQuestionInstance();
        return $question->morphToMany('tcCore\Tag', 'tag_relation')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function tagRelations()
    {
        return $this->hasMany('tcCore\TagRelation', 'tag_relation_id')->where('tag_relation_type', 'tcCore\Question');
    }

    public function getQuestionInstance()
    {
        $questionInstance = $this;

        while ($this->parentInstance !== null && get_class($questionInstance) !== 'tcCore\Question') {
            $questionInstance = $questionInstance->parentInstance;
        }

        return $questionInstance;
    }

    public function questionAttachments()
    {
        return $this->hasMany('tcCore\QuestionAttachment', 'question_id');
    }

    public function attachments()
    {
        return $this->belongsToMany('tcCore\Attachment', 'question_attachments', 'question_id', 'attachment_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function subject()
    {
        return $this->belongsTo('tcCore\Subject', 'subject_id');
    }

    public function educationLevel()
    {
        return $this->belongsTo('tcCore\EducationLevel', 'education_level_id');
    }

    public function questionAuthors()
    {
        return $this->hasMany('tcCore\QuestionAuthor', 'question_id');
    }

    public function authors()
    {
        return $this->belongsToMany('tcCore\User', 'question_authors', 'question_id', 'user_id')
            ->withTrashed()
            ->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])
            ->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function groupQuestionQuestions()
    {
        return $this->hasMany('tcCore\GroupQuestionQuestion');
    }

    public function questions()
    {
        return $this->belongsToMany('tcCore\Question', 'group_question_questions', 'group_question_id', 'question_id')->withPivot(['id', 'uuid', $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn(), 'order', 'maintain_position'])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function testQuestions()
    {
        return $this->hasMany('tcCore\TestQuestion', 'question_id');
    }

    public function derivedQuestion()
    {
        return $this->belongsTo('tcCore\Question', 'derived_question_id');
    }

    public function questionAttainments()
    {
        return $this->hasMany('tcCore\QuestionAttainment', 'question_id')->strict();
    }

    public function questionLearningGoals()
    {
        return $this->hasMany('tcCore\QuestionLearningGoal', 'question_id')->strict();
    }

    public function attainments()
    {
        return $this->belongsToMany('tcCore\Attainment', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function learningGoals()
    {
        return $this->belongsToMany('tcCore\LearningGoal', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function testTakes()
    {
        return $this->hasMany('tcCore\TestTake', 'discussing_question_id');
    }

    public function pValue()
    {
        return $this->hasMany('tcCore\PValue', 'question_id');
    }

    public function owner()
    {
        return $this->belongsTo('tcCore\SchoolLocation', 'owner_id');
    }

    /**
     * This relation is NOT ment to be used to get the test_questions,
     * but to get the questions of the test_take that are being discussed.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testTakeQuestions()
    {
        return $this->hasMany('tcCore\TestTakeQuestion', 'test_take_id');
    }

    public function getChangedIds()
    {
        return ['oldId' => $this->changedId, 'newId' => $this->getKey(), 'children' => $this->changedChildrenIds];
    }

    public function duplicate(array $attributes, $ignore = null)
    {
        $question = $this->replicate();
        $question->fill($attributes);

        if (isset($question->uuid)) {
            $question->uuid = Uuid::uuid4();
        }

        $question->setAttribute('derived_question_id', $this->getKey());
        if (!$question->save()) {
            return false;
        }
        foreach ($this->questionAttachments as $questionAttachment) {
            if ($ignore instanceof Attachment && $ignore->getKey() == $questionAttachment->getAttribute('attachment_id')) {
                continue;
            }

            if ($ignore instanceof QuestionAttachment && $ignore->getAttribute('attachment_id') == $questionAttachment->getAttribute('attachment_id') && $ignore->getAttribute('question_id') == $questionAttachment->getAttribute('question_id')) {
                continue;
            }
            $options = [];
            if (isset($attributes['questionAttachmentOptions'][$questionAttachment->attachment_id])) {
                $options = [
                    'options' => json_encode($attributes['questionAttachmentOptions'][$questionAttachment->attachment_id])
                ];
            }

            if (($newAttachment = $questionAttachment->duplicate($question, $options)) === false) {
                return false;
            }
        }

        $question->authors = $this->questionAuthors()->pluck('user_id')->all();
        $question->saveAuthors();

        if (!array_key_exists('tags', $attributes)) {
            $tags = $this->tags()->pluck('id')->all();

            if (array_key_exists('add_tag', $attributes)) {
                $tagId = Tag::where('name', $attributes['add_tag'])->value('id');
                if ($tagId) {
                    array_push($this->tags, $attributes['add_tag']);
                }
            }

            if (array_key_exists('delete_tag', $attributes)) {
                $tagId = Tag::where('name', $attributes['delete_tag'])->value('id');
                if ($tagId) {
                    if (($key = array_search($attributes['delete_tag'], $this->tags)) !== false) {
                        unset($this->tags[$key]);
                    }
                }
            }

            if ($tags) {
                if ($ignore instanceof Tag && ($key = array_search($ignore->getKey(), $tags)) !== false) {
                    unset($tags[$key]);
                }

                $question->tags()->attach($tags);
            }
        }

        collect(['attainments', 'learningGoals'])->each(fn($relation) => $this->addCurrentQuestionRelationToNewQuestion($question, $relation));

        return $question;
    }

    protected function saveAuthors()
    {
        $questionAuthors = $this->questionAuthors()->withTrashed()->get();
        $this->syncTcRelation($questionAuthors, $this->authors, 'user_id', function ($question, $userId) {
            QuestionAuthor::create(['question_id' => $question->getKey(), 'user_id' => $userId]);
        });

        $this->authors = null;
    }

    public function isClosedQuestion(): bool
    {
        return $this->isCitoQuestion();
    }

    protected function allOrNothingQuestion()
    {
        return $this->isCitoQuestion() || $this->all_or_nothing;
    }

    public function isCitoQuestion()
    {
        $question = $this;
        if (get_class($question) !== 'tcCore\Question') {
            $question = $this->getQuestionInstance();
        }

        return $question->scope === 'cito';
    }

    public function isWritingAssignment()
    {
        return $this instanceof OpenQuestion && $this->subtype === 'writing';
    }

    public function isWritingAssignmentWithSpellCheckAvailable()
    {
        return $this instanceof OpenQuestion && $this->subtype === 'writing' && $this->spell_check_available;
    }

    public function isDirtyAttainments()
    {
        return $this->isDirtyAttainmentsGeneric('attainments', 'questionAttainments');
    }

    public function isDirtyLearningGoals()
    {
        return $this->isDirtyAttainmentsGeneric('learning_goals', 'questionLearningGoals');
    }

    public function isDirtyAttainmentsGeneric($globalName, $questionRelation)
    {
        if ($this->$globalName === null) {
            return false;
        }

        $attainments = $this->$questionRelation()->pluck('attainment_id')->all();

        /////////////////
        //fix for TC-106
        //also fixed in the fill() method, but somehow that doesn't work
        //so we also fix it here, because this is where the error will start
        if (!is_array($this->$globalName)) {
            $this->$globalName = [$this->$globalName];
        }

        if (!is_array($attainments)) {
            $attainments = [$attainments];
        }
        /////////////////

        return (count($this->$globalName) != count($attainments) || array_diff($this->$globalName, $attainments));
    }

    protected function saveAttainments()
    {
        $questionAttainments = $this->questionAttainments()->withTrashed()->get();
        $this->syncTcRelation($questionAttainments, $this->attainments, 'attainment_id', function ($question, $attainmentId) {
            QuestionAttainment::create(['question_id' => $question->getKey(), 'attainment_id' => $attainmentId]);
        });

        $this->attainments = null;
    }

    protected function saveLearningGoals()
    {
        $questionLearningGoals = $this->questionLearningGoals()->withTrashed()->get();
        $this->syncTcRelation($questionLearningGoals, $this->learning_goals, 'attainment_id', function ($question, $attainmentId) {
            QuestionLearningGoal::create(['question_id' => $question->getKey(), 'attainment_id' => $attainmentId]);
        });

        $this->learning_goals = null;
    }

    public function isDirtyTags()
    {
        if ($this->tags === null) {
            return false;
        }

        $tags = $this->tagRelations()->pluck('tag_id')->all();

        return count($this->tags) != count($tags) || array_diff($this->tags, $tags);
    }

    public function isDirtyAttachmentOptions($request): bool
    {
        return (isset($request->all()['questionAttachmentOptions']));
    }

    public function isDirtyAnswerOptions($totalData): bool
    {
        if (!array_key_exists('answers', $totalData)) {
            return false;
        }

        switch ($this->type) {
            case 'MatchingQuestion':
                $requestAnswers = $this->convertMatchingAnswers($totalData['answers']);
                $questionClass = MatchingQuestion::class;
                break;
            case 'RankingQuestion':
                $requestAnswers = $this->trimAnswerOptions($totalData['answers']);
                $questionClass = RankingQuestion::class;
                break;
            case 'MultipleChoiceQuestion':
                $requestAnswers = $this->trimAnswerOptions($totalData['answers']);
                $questionClass = MultipleChoiceQuestion::class;
                break;
            default:
                return false;
        }

        try {
            $question = $questionClass::findOrFail($this->id);
            $answers = $this->convertAnswersFromQuestion($question, $this->type);
            return $requestAnswers != $answers;
        } catch (Exception $e) {
            return true;
        }
    }

    protected function saveTags()
    {
        $tags = $this->tagRelations()->withTrashed()->get();
        $this->syncTcRelation($tags, $this->tags, 'tag_id', function ($question, $tagId) {
            TagRelation::create(['tag_relation_id' => $question->getKey(), 'tag_relation_type' => 'tcCore\Question', 'tag_id' => $tagId]);
        });

        $this->attainments = null;
    }

    public function isUsed($ignoreRelationTo)
    {
        $uses = (new Question)->withTrashed()->where('derived_question_id', $this->getKey())->count();

        $testQuestions = TestQuestion::withTrashed()->where('question_id', $this->getKey());

        if ($ignoreRelationTo instanceof TestQuestion) {
            $testQuestions->where($ignoreRelationTo->getKeyName(), '!=', $ignoreRelationTo->getKey());
        }

        $uses += $testQuestions->count();

        $answers = Answer::withTrashed()->where('question_id', $this->getKey());
        if ($ignoreRelationTo instanceof Answer) {
            $answers->where($ignoreRelationTo->getKeyName(), '!=', $ignoreRelationTo->getKey());
        }
        $uses += $answers->count();

        $answerParentQuestions = AnswerParentQuestion::withTrashed()->where('group_question_id', $this->getKey());
        if ($ignoreRelationTo instanceof Answer) {
            $answerParentQuestions->where('answer_id', '!=', $ignoreRelationTo->getKey());
        }
        $uses += $answerParentQuestions->count();

        $groupQuestionQuestions = GroupQuestionQuestion::withTrashed()->where('question_id', $this->getKey());
        if ($ignoreRelationTo instanceof Question) {
            $groupQuestionQuestions->where('group_question_id', '!=', $ignoreRelationTo->getKey());
        }
        if ($ignoreRelationTo instanceof GroupQuestionQuestion) {
            $groupQuestionQuestions->where($ignoreRelationTo->getKeyName(), '!=', $ignoreRelationTo->getKey());
        }

        $uses += $groupQuestionQuestions->count();

        return $uses > 0;
    }


    public function isUsedInGroupQuestion($groupQuestionQuestionManager, $groupQuestionPivot)
    {
        return $groupQuestionQuestionManager->isUsed() || $this->isUsed($groupQuestionPivot);
    }

    public function scopeDifferentScenariosAndDemo($query, $filters = [])
    {
        $user = Auth::user();

        if ($user->isToetsenbakker()) {
            return $query->owner($user->schoolLocation);
        }

        if ($user->isA('Teacher')) {
            $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
            $query->join($this->switchScopeFilteredSubQueryForDifferentScenarios($user, $subject), function ($join) {
                $join->on('questions.id', '=', 't1.t2_id');
            });
            if (!is_null($subject)) {
                $query->where(function ($q) use ($user, $subject) {
                    $q->where(function ($query) use ($user, $subject) {
                        $query->where('questions.subject_id', $subject->getKey())->whereIn('questions.id', $user->questionAuthors()->select('question_id'));
                    })->orWhere('questions.subject_id', '<>', $subject->getKey());
                });
            }
        }

        return $query;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();
        $query = $this->differentScenariosAndDemo($query, $filters);

        $searchJoins = $this->handleSearchFilters($query, $filters);
        $filterJoins = $this->handleFilterParams($query, $user, $filters);

        $joins = array_merge($searchJoins, $filterJoins);

        $this->handleFilteredSorting($query, $sorting);
        $this->handleQueryJoins($query, array_unique($joins));

        return $query;
    }

    public function scopePublishedFiltered($query, $filters = [], $sorting = [])
    {
        $searchJoins = $this->handleSearchFilters($query, $filters);

        $this->handlePublishedFilterParams($query, $filters);
        $this->handleFilteredSorting($query, $sorting);
        $this->handleQueryJoins($query, array_unique($searchJoins));
        return $query;
    }

    public function performReorder($entities, $movedEntity, $attribute)
    {
        $order = $movedEntity->getAttribute($attribute);
        $movedPrimaryKey = $movedEntity->getKey();

        $i = 1;
        if ($order) {

            foreach ($entities as $entity) {
                $primaryKey = $entity->getKey();
                if (is_array($primaryKey) && is_array($movedPrimaryKey)) {
                    $matched = true;

                    foreach ($primaryKey as $key => $value) {
                        if (!array_key_exists($key, $movedPrimaryKey) || $value != $movedPrimaryKey[$key]) {
                            $matched = false;
                            break;
                        }
                    }

                    if ($matched) {
                        continue;
                    }
                } elseif (!is_array($primaryKey) && !is_array($movedPrimaryKey) && $primaryKey === $movedPrimaryKey) {
                    continue;
                }

                if ($i == $order) {
                    $i++;
                }

                $entity->setReorder(false);
                $entity->setAttribute($attribute, $i);
                $entity->save();
                $entity->setReorder(true);
                $i++;
            }

            if ($i < $order) {
                $movedEntity->setReorder(false);
                $movedEntity->setAttribute($attribute, $i);
                $movedEntity->save();
                $movedEntity->setReorder(true);
            }
        } else {
            foreach ($entities as $entity) {
                $primaryKey = $entity->getKey();
                if (is_array($primaryKey) && is_array($movedPrimaryKey)) {
                    $matched = true;

                    foreach ($primaryKey as $key => $value) {
                        if (!array_key_exists($key, $movedPrimaryKey) || $value !== $movedPrimaryKey[$key]) {
                            $matched = false;
                            continue;
                        }
                    }

                    if ($matched) {
                        continue;
                    }
                } elseif (!is_array($primaryKey) && !is_array($movedPrimaryKey) && $primaryKey === $movedPrimaryKey) {
                    continue;
                }

                $entity->setReorder(false);
                $entity->setAttribute($attribute, $i);
                $entity->save();
                $entity->setReorder(true);
                $i++;
            }

            $movedEntity->setReorder(false);
            $movedEntity->setAttribute($attribute, $i);
            $movedEntity->save();
            $movedEntity->setReorder(true);
        }
    }

    public static function findByUuid($uuid)
    {

        return Question::whereUuid($uuid)->first();

//        $question = OpenQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = DrawingQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = RankingQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = MatchingQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = CompletionQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = InfoscreenQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = MultipleChoiceQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = MatrixQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        $question = GroupQuestion::whereUuid($uuid)->first();
//        if (!empty($question)) {
//            return $question;
//        }
//
//        return null;
    }

    public function deleteAnswers() {}

    /**
     * @param $mainQuestion TestQuestion|GroupQuestionQuestion
     * @param $answers
     * @return array
     * @throws \Exception
     */
    public function addAnswers($mainQuestion, $answers) {}

    private function convertMatchingAnswers($answers)
    {
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            if ($answer['left'] == '') {
                continue;
            }
            $this->addReturnArrayItemMatching($answer['left'], 'LEFT', $returnArray);
            $this->addReturnArrayItemMatching($answer['right'], 'RIGHT', $returnArray);
        }
        return $returnArray;
    }

//    @TODO refactor this method to MatchingQuestion class?
    private function convertMatchingAnswersFromQuestion($question)
    {
        $returnArray = [];
        $answers = $question->matchingQuestionAnswers->toArray();
        foreach ($answers as $key => $answer) {
            $returnArray[] = ['answer' => $answer['answer'],
                              'type'   => $answer['type'],
            ];
        }
        return $returnArray;
    }

    protected function trimAnswerOptions($answers)
    {
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            if (!array_key_exists('answer', $answer)) {
                $returnArray[] = $answer;
                continue;
            }
            if ($answer['answer'] == '') {
                continue;
            }
            $returnArray[] = $answer;
        }
        return $returnArray;
    }


    private function convertRankingAnswersFromQuestion($question)
    {
        $answers = $question->rankingQuestionAnswers->toArray();
        return $this->convertAnswersFromQuestion($answers, ['order', 'answer']);
    }

    private function convertMultipleChoiceAnswersFromQuestion($question)
    {
        $answers = $question->multipleChoiceQuestionAnswers->toArray();
        $ignoreOrder = false;
        if ($question->subtype == 'TrueFalse') {
            $ignoreOrder = true;
        }
        return $this->convertAnswersFromQuestion($answers, ['order', 'answer', 'score'], $ignoreOrder);
    }

    private function convertAnswersFromQuestion($answers, $params, $ignoreOrder = false)
    {
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            $item = [];
            foreach ($params as $param) {
                if ($param == 'order' && $ignoreOrder) {
                    $item['order'] = 0;
                    continue;
                }
                if ($param == 'order') {
                    $item['order'] = ($key + 1);
                    continue;
                }
                if (!array_key_exists($param, $answer)) {
                    throw new Exception('unknown answer key');
                    continue;
                }
                $item[$param] = $answer[$param];
            }
            $returnArray[] = $item;
        }
        return $returnArray;
    }

    private function addReturnArrayItemMatching($answer, $type, &$returnArray): void
    {
        $answers = explode("\n", str_replace(["\r\n", "\n\r", "\r"], "\n", $answer));
        foreach ($answers as $answerPart) {
            $returnArray[] = ['answer' => $answerPart,
                              'type'   => $type,
            ];
        }

    }


    public function getQuestionHtml()
    {
        return $this->getQuestionInstance()->question;
    }

    public function getCaptionAttribute()
    {
        return $this->type_name;
    }

    public function getQuestionCount()
    {
        return 1;
    }

    public function getGroupQuestionIdByTest($testId)
    {
        $groupQuestions = GroupQuestionQuestion::whereQuestionId($this->getKey())->get();
        if ($groupQuestions->count() > 1) {
            return TestQuestion::whereTestId($testId)
                ->whereIn('question_id', $groupQuestions->pluck('group_question_id'))
                ->first()
                ->question
                ->getKey();
        }
        return $groupQuestions->first()?->groupQuestion->getKey();
    }

    public function getTotalDataForTestQuestionUpdate($request)
    {
        return $request->all();
    }

    public function updateWithRequest($request, $testQuestion)
    {
        $this->fill($this->getTotalDataForTestQuestionUpdate($request));
        $this->handleOnlyAddToDatabaseFieldIsModified($request);
        $this->handleAnyOtherFieldsAreModified($testQuestion, $request);
        return $this;
    }

    public function updateWithRequestGroup($request, $groupQuestionPivot, $groupQuestionQuestionManager)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        $this->fill($totalData);
        $this->handleOnlyAddToDatabaseFieldIsModified($request);
        $this->handleAnyOtherFieldsAreModifiedWithinGroupQuestion($request, $groupQuestionPivot, $groupQuestionQuestionManager);

    }

    public function getCompletionAnswerDirty($request)
    {
        return false;
    }

    public function getQuestionData($request)
    {
        return [];
    }

    public function handleOnlyAddToDatabaseFieldIsModified($request)
    {
        $baseModel = $this->getQuestionInstance();
        if ($this->onlyAddToDatabaseFieldNeedsToBeUpdated($request)) {
            if (!$baseModel->save()) {
                throw new QuestionException('Failed to save question');
            }
            $this->onlyAddToDatabaseFieldIsModified = true;
        }
    }

    public function handleAnyOtherFieldsAreModified(TestQuestion $testQuestion, $request)
    {
        if (!$this->needsToBeUpdated($request)) {
            return;
        }
        if ($this->isUsed($testQuestion)) {
            $this->handleDuplication($request);
            return;
        }
        if ($this->questionAttachmentOptionsNeedSaving($request)) {
            $this->handleAttachmentOptionsSaving($request);
        }

        $this->saveBothBaseModelAndQuestion();
    }

    public function handleAnyOtherFieldsAreModifiedWithinGroupQuestion($request, $groupQuestionPivot, $groupQuestionQuestionManager)
    {
        if (!$this->needsToBeUpdated($request)) {
            return;
        }
        if ($this->isUsedInGroupQuestion($groupQuestionQuestionManager, $groupQuestionPivot)) {
            $this->handleDuplication($request);
            return;
        }
        if ($this->questionAttachmentOptionsNeedSaving($request)) {
            $this->handleAttachmentOptionsSaving($request);
        }

        $this->saveBothBaseModelAndQuestion();

    }

    protected function saveBothBaseModelAndQuestion()
    {
        $baseModel = $this->getQuestionInstance();

        $var = $baseModel->save();
        if (!$var) {
            throw new QuestionException('Failed to save question');
        }
        $this->save();
    }

    protected function handleAttachmentOptionsSaving($request)
    {
        $this->questionAttachments->each(function ($questionAttachment) use ($request) {
            if (isset($request['questionAttachmentOptions'][$questionAttachment->attachment_id])) {
                $questionAttachment->options = json_encode($request['questionAttachmentOptions'][$questionAttachment->attachment_id]);
            }
        });
        $this->questionAttachments->each->save();
    }

    protected function questionAttachmentOptionsNeedSaving($request)
    {
        return isset($request['questionAttachmentOptions']);
    }

    public function getKeyAfterPossibleDuplicate()
    {
        if (!$this->duplicateQuestionKey) {
            return $this->getKey();
        }
        return $this->duplicateQuestionKey;
    }

    public function flushDuplicateQuestionKey()
    {
        $this->duplicateQuestionKey = false;
    }

    public function handleAnswersAfterOwnerModelUpdate($ownerModel, $request)
    {
        $baseModel = $this->getQuestionInstance();
        if (!self::usesDeleteAndAddAnswersMethods($baseModel->type)) {
            return;
        }
        if (!$this->needsToBeUpdated($request)) {
            return;
        }
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        if (!array_key_exists('answers', $totalData)) {
            return;
        }
        $this->deleteAnswers($this);
        $this->addAnswers($ownerModel, $totalData['answers']);
    }

    protected function handleDuplication($request)
    {
        $totalData = $this->getQuestionDataBeforeDuplicationByRequest($request);

        $originalQuestionId = $this->getKey();

        $question = $this->duplicate($totalData);
        if ($question === false) {
            throw new QuestionException('Failed to duplicate question');
        }
        $this->duplicateQuestionKey = $question->getKey();
        $this->addQuestionToAuthor($question);

        if ($totalData['draft'] === false && QuestionHelper::belongsOnlyToDraftTests(
                questionId: $originalQuestionId,
                excludeTestId: $request['test_id']
            )) {
            QuestionHelper::setToDraft($originalQuestionId);
        }
    }

    public function onlyAddToDatabaseFieldNeedsToBeUpdated($request)
    {
        $baseModel = $this->getQuestionInstance($request);
        if (!$this->needsToBeUpdated($request)) {
            return false;
        }
        if (count($this->getDirty()) > 0) {
            return false;
        }
        if (!array_key_exists('add_to_database', $baseModel->getDirty())) {
            return false;
        }
        if (count($baseModel->getDirty()) === 1) {
            return true;
        }
        return false;
    }

    public function needsToBeUpdated($request)
    {
        if ($this->onlyAddToDatabaseFieldIsModified) {
            return false;
        }
        if ($this->isDirty()) {
            return true;
        }
        $baseModel = $this->getQuestionInstance();
        if ($baseModel->isDirty()) {
            return true;
        }
        if ($baseModel->isDirtyAttainments()) {
            return true;
        }
        if ($baseModel->isDirtyLearningGoals()) {
            return true;
        }
        if ($baseModel->isDirtyTags()) {
            return true;
        }
        if ($baseModel->isDirtyAttachmentOptions($request)) {
            return true;
        }
        return false;
    }

    protected function addQuestionToAuthor($question)
    {
        $var = QuestionAuthor::addAuthorToQuestion($question);
        if (!$var) {
            throw new QuestionException('Failed to attach author to question');
        }
    }

    public function handleGroupDuplication($request, $groupQuestionQuestionManager, $groupQuestionPivot)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        $this->fill($totalData);
        if (!$this->isUsedInGroupQuestion($groupQuestionQuestionManager, $groupQuestionPivot)) {
            return;
        }
        if (!$this->needsToBeUpdated($request)) {
            return;
        }
        $testQuestion = $groupQuestionQuestionManager->prepareForChange($groupQuestionPivot);
        $groupQuestionPivotCopy = $groupQuestionPivot->duplicate(
            $groupQuestionQuestionManager->getQuestionLink()->question,
            [
                'group_question_id' => $groupQuestionQuestionManager->getQuestionLink()->getAttribute('group_question')
            ]
        );
        $questionCopy = $groupQuestionPivotCopy->question;
        $questionCopy->fill($totalData);
//        $questionCopy->save();
//        $groupQuestionPivotCopy->setAttribute('group_question_id', $testQuestion->getAttribute('question_id'));
//        $groupQuestionPivotCopy->save();
        $this->groupQuestionPivot = $groupQuestionPivotCopy;
    }

    public function getGroupQuestionPivotAfterPossibleDuplication($groupQuestionPivot)
    {
        if ($this->groupQuestionPivot) {
            return $this->groupQuestionPivot;
        }
        return $groupQuestionPivot;
    }

    public function convertInlineImageSources()
    {
        $questionHtmlConverter = new QuestionHtmlConverter($this->getQuestionHtml());

        return $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image', self::INLINE_IMAGE_PATTERN);
    }

    public function getConvertedQuestionHtmlAttribute()
    {
        return $this->convertInlineImageSources();
    }

    public function getQuestionAttainmentsAsArray()
    {
        return $this->questionAttainments->map(function ($relation) {
            return $relation->attainment_id;
        })->toArray();
    }

    public function getQuestionLearningGoalsAsArray()
    {
        return $this->questionLearningGoals->map(function ($relation) {
            return $relation->attainment_id;
        })->toArray();
    }

    private function getQueryGetItemsFromSchoolLocationAuthoredByUser($user)
    {
        return sprintf('select distinct t2.id as t2_id  /* select all questions from schoollocation authored by user */
                                from
                                   `questions` as t2
                                        left join question_authors
                                            on t2.id = question_authors.question_id
                                        inner join (
                                            select distinct subjects.id as subject_id
                                            from subjects
                                                left join sections
                                                    on subjects.section_id = sections.id
                                                left join school_location_sections as t9
                                                    on t9.section_id = sections.id
                                            where
                                                subjects.deleted_at is null
                                                and
                                                t9.school_location_id = %d
                                                        ) as s2
                                                    on t2.subject_id = s2.subject_id
                                            where question_authors.user_id = %d',
            $user->school_location_id,
            $user->id);
    }

    private function getQueryGetItemsFromSectionWithinSchoolLocation($user, $demoSubject)
    {
        return sprintf('select distinct t2.id as t2_id /* select tests from active schoollocation with subjects that fall under the section the user is member of */
                                            from
                                               `questions` as t2
                                               inner join (
                                                        select distinct t8.id as subject_id
                                                        from subjects
                                                            left join sections
                                                                on subjects.section_id = sections.id
                                                            left join subjects as t8
                                                                on sections.id = t8.section_id
                                                            left join school_location_sections as t9
                                                                on t9.section_id = sections.id
                                                            left join teachers
                                                                on subjects.id = teachers.subject_id
                                                        where
                                                            subjects.deleted_at is null
                                                                and
                                                            teachers.user_id = %d
                                                                and
                                                            teachers.deleted_at is null
                                                                and
                                                            t9.school_location_id = %d
                                                            ) as s2
                                                    on t2.subject_id = s2.subject_id',
            $user->id,
            $user->school_location_id
        );
    }


    private function getQueryGetItemsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user, $demoSubject)
    {
        return sprintf('select distinct t2.id as t2_id  /* select questions from all schoollocations authored by user and currently taught in active schoollocation */
                                            from
                                               `questions` as t2
                                                    left join question_authors
                                                        on t2.id = question_authors.question_id
                                                    inner join (
                                                         select distinct t3.id as subject_id
                                                        from subjects
                                                            left join sections
                                                                on subjects.section_id = sections.id
                                                            inner join subjects as t3
                                                                on subjects.base_subject_id = t3.base_subject_id
                                                            left join school_location_sections as t10
                                                                on sections.id = t10.section_id
                                                            left join teachers
                                                                on subjects.id = teachers.subject_id
                                                        where
                                                            subjects.deleted_at is null
                                                                and
                                                            teachers.user_id = %d
                                                                and
                                                            teachers.deleted_at is null
                                                                and
                                                            t10.school_location_id = %d
                                                        ) as s2
                                                    on t2.subject_id = s2.subject_id
                                            where question_authors.user_id = %d ',
            $user->id,
            $user->school_location_id,
            $user->id
        );
    }

    public function getTitleAttribute()
    {
        $withMath = strip_tags(html_entity_decode($this->getQuestionHtml()), ['math']);//,'msqrt','msub','msup','mo','mfrac','mrow','mi','mfenced','mroot','mover','munderover','mn','mtd','mtr','mtable','msrow','msline','mstack','mlongdiv','msgroup','mstyle','mmultiscripts','mprescripts','none','msubsup','munder','menclose','mtext','mspace']);

        return preg_replace('#(<math.*?>).*?(</math>)#', '', $withMath);
    }

    public function getTypeNameAttribute()
    {
        return __('question.' . Str::lower($this->type . ($this->subtype ?? '')));
    }

    public function getAuthorNamesString(): string
    {
        return $this->getAuthorNamesCollection()->implode(', ');
    }

    public function getAuthorNamesCollection()
    {
        return $this->getAuthorsWithMainFirst()->map(function ($author) {
            return $author->getFullNameWithAbbreviatedFirstName();
        });
    }

    public static function addOwnerIdToAllQuestions()
    {
        Question::withTrashed()
            ->where(function ($query) {
                $query->whereNotIn('type', ['BlaBla', 'BlaBla-2', 'BlaBla-3'])
                    ->whereNull('owner_id');
            })
            ->chunkById(100, function ($questions) {
                foreach ($questions as $question) {
                    Question::whereId($question->id)
                        ->withTrashed()
                        ->update(['owner_id' =>
                                      SchoolLocationSection::select('school_location_id')
                                          ->where('section_id', function ($query) use ($question) {
                                              $query->select('section_id')
                                                  ->from((new Subject)->getTable())
                                                  ->where('id', function ($query) use ($question) {
                                                      $query->select('subject_id')
                                                          ->from((new Question)->getTable())
                                                          ->where('id', $question->id);
                                                  });
                                          })
                                          ->first()
                                          ->school_location_id
                        ]);
                }
            });
    }

    private static function addOwnerId($question)
    {
        try {
            $schoolLocationSection = SchoolLocationSection::select('school_location_id')
                ->where('section_id', function ($query) use ($question) {
                    $query->select('section_id')
                        ->from((new Subject)->getTable())
                        ->where('id', function ($query) use ($question) {
                            $query->select('subject_id')
                                ->from((new Question)->getTable())
                                ->where('id', $question->id);
                        });
                })
                ->first();

            $question->owner_id = ($schoolLocationSection ? $schoolLocationSection->school_location_id : Auth::user()->school_location_id);
        } catch (\Throwable $e) {
            $question->owner_id = Auth::user()->school_location_id;
        }
    }

    public function isType($type): bool
    {
        return Str::of($this->type)->lower()->contains(Str::lower($type));
    }

    public function isInTest($testUuid, $strict = false): bool
    {
        $test = Test::whereUuid($testUuid)->first();
        if (!$test) {
            return false;
        }

        if ($strict) {
            return $test->testQuestions()->where('question_id', $this->getKey())->exists();
        }

        return $test->testQuestions()
            ->selectRaw('1')
            ->join('questions as q', 'q.id', '=', 'test_questions.question_id')
            ->where('test_questions.question_id', $this->getKey())
            ->orWhere('q.derived_question_id', $this->getKey())
            ->exists();
    }

    public function hasCmsPreview()
    {
        return !$this->isType('matrix');
    }

    public function attachToParentInTest($testUuid, $testQuestionUuidForGroupQuestion = null)
    {
        $this->getConnectionModelToAttachTo($testUuid, $testQuestionUuidForGroupQuestion)->create([
            'question_id'       => $this->getKey(),
            'maintain_position' => 0,
            'discuss'           => 1
        ]);
    }

    private function getConnectionModelToAttachTo($testUuid, $testQuestionUuidForGroupQuestion)
    {
        if ($testQuestionUuidForGroupQuestion) {
            $groupQuestionId = TestQuestion::whereUuid($testQuestionUuidForGroupQuestion)->pluck('question_id')->first();
            $connectionModel = GroupQuestion::whereId($groupQuestionId)->firstOrFail()->groupQuestionQuestions();
        } else {
            $connectionModel = Test::whereUuid($testUuid)->firstOrFail()->testQuestions();
        }

        return $connectionModel;
    }

    public function needsCleanCopy(): bool
    {
        return filled($this->getQuestionInstance()->scope); // TODO: is this enough/too much?
//        return $this->isNationalItem(); //TODO implement for creathlon & make dynamic
    }

    public function isNationalItem(): bool
    {
        return collect(Test::NATIONAL_ITEMBANK_SCOPES)->contains($this->getQuestionInstance()->scope);
    }

    public function createCleanCopy($education_level_id, $education_level_year, $subject_id, $draft, User $forUser)
    {
        $newQuestion = $this->duplicate($this->getAttributes());

        $newQuestionInstance = $newQuestion->getQuestionInstance();
        $newQuestionInstance->scope = null;
        $newQuestionInstance->derived_question_id = null;
        $newQuestionInstance->education_level_id = $education_level_id;
        $newQuestionInstance->education_level_year = $education_level_year;
        $newQuestionInstance->subject_id = $subject_id;
        $newQuestionInstance->draft = $draft;
        $newQuestionInstance->add_to_database = false;
        $newQuestionInstance->add_to_database_disabled = true;
        $newQuestionInstance->save();
        QuestionAuthor::addAuthorToQuestion($newQuestion, $forUser->getKey());
        $newQuestion->refresh();

        if ($newQuestion->type == 'GroupQuestion') {
            foreach ($newQuestion->groupQuestionQuestions as $key => $groupQuestionQuestion) {
                $oldQuestionInGroup = $groupQuestionQuestion->question;
                $newQuestionInGroup = $oldQuestionInGroup->createCleanCopy($education_level_id, $education_level_year, $subject_id, $draft, $forUser);
                $groupQuestionQuestion->question_id = $newQuestionInGroup->id;
                $groupQuestionQuestion->save();
            }
        }

        return $newQuestion->fresh();
    }

    public function getAuthorsWithMainFirst()
    {
        return $this->authors()
            ->get()
            ->sortByDesc(function ($author) {
                return $author->user_id === $this->author_id ? 1 : 0;
            })
            ->values();
    }

    private function addCurrentQuestionRelationToNewQuestion(Question $question, $relationName)
    {
        $pivotTable = 'question' . Str::ucfirst($relationName);

        if ($this->$pivotTable && $question->$pivotTable()->doesntExist()) {
            $params = $this->$pivotTable->map(function ($relation) use ($question) {
                $relation->question_id = $question->getKey();
                return $relation->toArray();
            });
            $question->$pivotTable()->createMany($params);
        }
    }

    public function scopeDraft($query)
    {
        return $query->where('draft', true);
    }

    public function scopePublished($query)
    {
        return $query->where('draft', false);
    }

    public function isSubType($type): bool
    {
        return Str::lower($this->subtype) === Str::lower($type);
    }


    private function getQuestionDataBeforeDuplicationByRequest($request)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);

        if (array_key_exists('test_draft', $totalData)) {
            $totalData['draft'] = $totalData['test_draft'];
        }

        return $totalData;
    }

    public function scopeOwner($query, SchoolLocation $schoolLocation)
    {
        return $query->where('owner_id', $schoolLocation->getKey());
    }

    public function scopeTaxonomies($query, array $valuesPerTaxonomy)
    {
        return $query->where(function ($query) use ($valuesPerTaxonomy) {
            collect($valuesPerTaxonomy)->each(function ($values, $column) use ($query, $valuesPerTaxonomy) {
                $whereMethod = array_key_first($valuesPerTaxonomy) === $column ? 'whereIn' : 'orWhereIn';
                $query->$whereMethod(
                    'questions.' . $column,
                    $values
                );
            });
        });
    }

    public static function setAttributesFromParentModel(Question $question, Test|GroupQuestion $parent)
    {
        $questionInstance = $question->getQuestionInstance();
        if ($questionInstance->getAttribute('subject_id') === null) {
            $questionInstance->setAttribute('subject_id', $parent->subject->getKey());
        }

        if ($questionInstance->getAttribute('education_level_id') === null) {
            $questionInstance->setAttribute('education_level_id', $parent->educationLevel->getKey());
        }

        if ($questionInstance->getAttribute('education_level_year') === null) {
            $questionInstance->setAttribute('education_level_year', $parent->getAttribute('education_level_year'));
        }

        if ($questionInstance->getAttribute('draft') === null) {
            $questionInstance->setAttribute('draft', $parent->getAttribute('draft'));
        }
    }

    public function isFullyAnswered(Answer $answer): bool
    {
        return (bool)$answer->done;
    }

    public function getGroupQuestion(TestTake $testTake) : false|GroupQuestion
    {
        return GroupQuestion::select('group_questions.*')
                             ->join('group_question_questions', 'group_questions.id', '=', 'group_question_questions.group_question_id')
                             ->join('test_questions', 'test_questions.question_id', '=', 'group_questions.id')
                             ->join('tests', 'tests.id', '=', 'test_questions.test_id')
                             ->join('test_takes', 'test_takes.test_id', '=', 'tests.id')
                             ->where('test_takes.id', $testTake->getKey())
                             ->where('group_question_questions.question_id', $this->getKey())
                             ->first() ?? false;
    }
}
