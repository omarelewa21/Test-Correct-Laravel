<?php namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Models\MtiBaseModel;
use tcCore\Scopes\QuestionAttainmentScope;
use tcCore\Services\QuestionHtmlConverter;
use tcCore\Traits\ExamSchoolQuestionTrait;
use tcCore\Traits\UserContentAccessTrait;
use tcCore\Traits\UuidTrait;

class Question extends MtiBaseModel {
    use SoftDeletes;
    use UuidTrait;
    use ExamSchoolQuestionTrait;
    use UserContentAccessTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'all_or_nothing' => 'boolean',
    ];

    public $mtiBaseClass = 'tcCore\Question';
    public $mtiClassField = 'type';
    public $mtiParentTable = 'questions';


    const INLINE_IMAGE_PATTERN = '/custom/imageload.php?filename=';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
    protected $fillable = [ 'subject_id',
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
        return collect(['completionquestion', 'matchingquestion', 'rankingquestion','matrixquestion','multiplechoicequestion'])->contains(strtolower($questionType));
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (get_class($this) === 'tcCore\Question') {
            if(array_key_exists('authors', $attributes)) {
                $this->authors = $attributes['authors'];
            } elseif(array_key_exists('add_author', $attributes) || array_key_exists('delete_author', $attributes)) {
                $this->authors = $this->questionAuthors()->pluck('user_id')->all();
                if (array_key_exists('add_author', $attributes)) {
                    array_push($this->authors, $attributes['add_author']);
                }

                if (array_key_exists('delete_author', $attributes)) {
                    if(($key = array_search($attributes['delete_author'], $this->authors)) !== false) {
                        unset($this->authors[$key]);
                    }
                }
            }

            if(array_key_exists('attainments', $attributes)) {
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
            } elseif(array_key_exists('add_attainment', $attributes) || array_key_exists('delete_attainment', $attributes)) {
                $this->attainment = $this->questionAttainments()->pluck('attainment_id')->all();
                if (array_key_exists('add_attainment', $attributes)) {
                    array_push($this->attainments, $attributes['add_attainment']);
                }

                if (array_key_exists('delete_attainment', $attributes)) {
                    if(($key = array_search($attributes['delete_attainment'], $this->attainments)) !== false) {
                        unset($this->attainments[$key]);
                    }
                }
            }

            if(array_key_exists('learning_goals', $attributes)) {
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
        static::creating(function(Question $question) {
            self::addOwnerId($question->getQuestionInstance());
        });
        static::created(function(Question $question)
        {
            QuestionAuthor::addAuthorToQuestion($question);
        });
        static::saving(function(Question $question)
        {
            $question->handleExamPublishingQuestion();
        });

        static::saved(function(Question $question)
        {
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

    public function getQuestionInstance() {
        $questionInstance = $this;

        while ($this->parentInstance !== null && get_class($questionInstance) !== 'tcCore\Question') {
            $questionInstance = $questionInstance->parentInstance;
        }

        return $questionInstance;
    }

    public function questionAttachments() {
        return $this->hasMany('tcCore\QuestionAttachment', 'question_id');
    }

    public function attachments() {
        return $this->belongsToMany('tcCore\Attachment', 'question_attachments', 'question_id', 'attachment_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function subject() {
        return $this->belongsTo('tcCore\Subject', 'subject_id');
    }

    public function educationLevel() {
        return $this->belongsTo('tcCore\EducationLevel', 'education_level_id');
    }

    public function questionAuthors() {
        return $this->hasMany('tcCore\QuestionAuthor', 'question_id');
    }

    public function authors() {
        return $this->belongsToMany('tcCore\User', 'question_authors', 'question_id', 'user_id')
                ->withTrashed()
                ->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])
                ->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function groupQuestionQuestions() {
        return $this->hasMany('tcCore\GroupQuestionQuestion');
    }

    public function questions() {
        return $this->belongsToMany('tcCore\Question', 'group_question_questions', 'group_question_id', 'question_id')->withPivot(['id', 'uuid', $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn(), 'order', 'maintain_position'])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function testQuestions() {
            return $this->hasMany('tcCore\TestQuestion', 'question_id');
    }

    public function derivedQuestion() {
        return $this->belongsTo('tcCore\Question', 'derived_question_id');
    }

    public function questionAttainments() {
        return $this->hasMany('tcCore\QuestionAttainment', 'question_id')->strict();
    }

    public function questionLearningGoals() {
        return $this->hasMany('tcCore\QuestionLearningGoal', 'question_id')->strict();
    }

    public function attainments() {
        return $this->belongsToMany('tcCore\Attainment', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function learningGoals() {
        return $this->belongsToMany('tcCore\LearningGoal', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function testTakes() {
        return $this->hasMany('tcCore\TestTake', 'discussing_question_id');
    }

    public function pValue() {
        return $this->hasMany('tcCore\PValue');
    }

    public function owner() {
        return $this->belongsTo('tcCore\SchoolLocation', 'owner_id');
    }

    public function getChangedIds() {
        return ['oldId' => $this->changedId, 'newId' => $this->getKey(), 'children' => $this->changedChildrenIds];
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();
        $question->fill($attributes);

        if (isset($question->uuid)) {
            $question->uuid = Uuid::uuid4();
        }

        $question->setAttribute('derived_question_id', $this->getKey());
        if (!$question->save()) {
            return false;
        }
        foreach($this->questionAttachments as $questionAttachment) {
            if ($ignore instanceof Attachment && $ignore->getKey() == $questionAttachment->getAttribute('attachment_id')) {
                continue;
            }

            if ($ignore instanceof QuestionAttachment && $ignore->getAttribute('attachment_id') == $questionAttachment->getAttribute('attachment_id') && $ignore->getAttribute('question_id') == $questionAttachment->getAttribute('question_id')) {
                continue;
            }

            if (($newAttachment = $questionAttachment->duplicate($question, [])) === false) {
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

        return $question;
    }

    protected function saveAuthors() {
        $questionAuthors = $this->questionAuthors()->withTrashed()->get();
        $this->syncTcRelation($questionAuthors, $this->authors, 'user_id', function($question, $userId) {
            QuestionAuthor::create(['question_id' => $question->getKey(), 'user_id' => $userId]);
        });

        $this->authors = null;
    }

    protected function isClosedQuestion()
    {
//        $question = $this;
//        if (get_class($question) !== 'tcCore\Question') {
//            $question = $this->getQuestionInstance();
//        }
//
//        return $question->scope === 'cito';
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

    public function isDirtyAttainments() {
        return $this->isDirtyAttainmentsGeneric('attainments','questionAttainments');
        if ($this->attainments === null) {
            return false;
        }

        $attainments = $this->questionAttainments()->pluck('attainment_id')->all();

        /////////////////
        //fix for TC-106
        //also fixed in the fill() method, but somehow that doesn't work
        //so we also fix it here, because this is where the error will start
        if (!is_array($this->attainments)) {
            $this->attainments = [$this->attainments];
        }

        if (!is_array($attainments)) {
            $attainments = [$attainments];
        }
        /////////////////

        if (count($this->attainments) != count($attainments) || array_diff($this->attainments, $attainments)) {
            return true;
        } else {
            return false;
        }
    }

    public function isDirtyLearningGoals() {
        return $this->isDirtyAttainmentsGeneric('learning_goals','questionLearningGoals');
    }

    public function isDirtyAttainmentsGeneric($globalName,$questionRelation) {
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

        if (count($this->$globalName) != count($attainments) || array_diff($this->$globalName, $attainments)) {
            return true;
        } else {
            return false;
        }
    }

    protected function saveAttainments() {
        $questionAttainments = $this->questionAttainments()->withTrashed()->get();
        $this->syncTcRelation($questionAttainments, $this->attainments, 'attainment_id', function($question, $attainmentId) {
            QuestionAttainment::create(['question_id' => $question->getKey(), 'attainment_id' => $attainmentId]);
        });

        $this->attainments = null;
    }

    protected function saveLearningGoals() {
        $questionLearningGoals = $this->questionLearningGoals()->withTrashed()->get();
        $this->syncTcRelation($questionLearningGoals, $this->learning_goals, 'attainment_id', function($question, $attainmentId) {
            QuestionLearningGoal::create(['question_id' => $question->getKey(), 'attainment_id' => $attainmentId]);
        });

        $this->learning_goals = null;
    }

    public function isDirtyTags() {
        if ($this->tags === null) {
            return false;
        }

        $tags = $this->tagRelations()->pluck('tag_id')->all();

        if (count($this->tags) != count($tags) || array_diff($this->tags, $tags)) {
            return true;
        } else {
            return false;
        }
    }

    public function isDirtyAnswerOptions($totalData){
        if(!array_key_exists('answers',$totalData)){
            return false;
        }
        switch($this->type){
            case 'MatchingQuestion':
                $requestAnswers = $this->convertMatchingAnswers($totalData['answers']);
                try{
                    $question = MatchingQuestion::findOrFail($this->id);
                    $answers = $this->convertMatchingAnswersFromQuestion($question);
                    if($requestAnswers==$answers){
                        return false;
                    }
                }catch(Exception $e){
                    return true;
                }
                return true;
            break;
            case 'RankingQuestion':
                $requestAnswers = $this->trimAnswerOptions($totalData['answers']);
                try{
                    $question = RankingQuestion::findOrFail($this->id);
                    $answers = $this->convertRankingAnswersFromQuestion($question);
                    if($requestAnswers==$answers){
                        return false;
                    }
                }catch(Exception $e){
                    return true;
                }
                return true;
            break;
            case 'MultipleChoiceQuestion':
                $requestAnswers = $this->trimAnswerOptions($totalData['answers']);
                try{
                    $question = MultipleChoiceQuestion::findOrFail($this->id);
                    $answers = $this->convertMultipleChoiceAnswersFromQuestion($question);
                    if($requestAnswers==$answers){
                        return false;
                    }
                }catch(Exception $e){
                    return true;
                }
                return true;
            break;
            default:
                return false;
            break;
        }
    }

    protected function saveTags() {
        $tags = $this->tagRelations()->withTrashed()->get();
        $this->syncTcRelation($tags, $this->tags, 'tag_id', function($question, $tagId) {
            TagRelation::create(['tag_relation_id' => $question->getKey(), 'tag_relation_type' => 'tcCore\Question', 'tag_id' => $tagId]);
        });

        $this->attainments = null;
    }

    public function isUsed($ignoreRelationTo) {


        //$uses = Question::withTrashed()->where('derived_question_id', $this->getKey())->count();

        $uses = (new Question)->withTrashed()->where('derived_question_id', $this->getKey())->count();
        //Log::debug('Is used for question #'.$this->getKey());
        //Log::debug('Derived Questions = '.$uses);

        $testQuestions = TestQuestion::withTrashed()->where('question_id', $this->getKey());

        if ($ignoreRelationTo instanceof TestQuestion) {
            $testQuestions->where($ignoreRelationTo->getKeyName(), '!=', $ignoreRelationTo->getKey());
        }

        $uses += $testQuestions->count();

        $answers = Answer::withTrashed()->where('question_id', $this->getKey());
        if ($ignoreRelationTo instanceof Answer) {
            $answers->where($ignoreRelationTo->getKeyName(), '!=', $ignoreRelationTo->getKey());
        }
        //Log::debug('Answers = '.$answers->count());
        $uses += $answers->count();

        $answerParentQuestions = AnswerParentQuestion::withTrashed()->where('group_question_id', $this->getKey());
        if ($ignoreRelationTo instanceof Answer) {
            $answerParentQuestions->where('answer_id', '!=', $ignoreRelationTo->getKey());
        }
        //Log::debug('Answer Parent Questions = '.$answerParentQuestions->count());
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


    public function isUsedInGroupQuestion($groupQuestionQuestionManager,$groupQuestionPivot)
    {
        if($groupQuestionQuestionManager->isUsed()){
            return true;
        }
        if($this->isUsed($groupQuestionPivot)){
            return true;
        }
        return false;
    }

    public function scopeDifferentScenariosAndDemo($query, $filters = []){
//        $roles = $this->getUserRoles();
        $user = Auth::user();
//        $schoolLocation = SchoolLocation::find($user->getAttribute('school_location_id'));
        if ($user->isA('Teacher')) {
            $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
            $query->join($this->switchScopeFilteredSubQueryForDifferentScenarios($user,$subject), function ($join) {
                $join->on('questions.id', '=', 't1.t2_id');
            });
            if(!is_null($subject)){
                $query->where(function ($q) use ($user,$subject) {
                    $q->where(function ($query) use ($user, $subject) {
                        $query->where('questions.subject_id', $subject->getKey())->whereIn('questions.id',$user->questionAuthors()->pluck('question_id'));
                    })->orWhere('questions.subject_id', '<>', $subject->getKey());
                });
            }
//            $query->orWhere(function($q) use ($user, $subject){
//                // subject id = $subject->getKey() together with being an owner through the question_authors table
//                $q->where('subject_id',$subject->getKey());
//                $q->whereIn('questions.id',$user->questionAuthors()->pluck('question_id'));
//            });
//            // or subject_id in list AND subject not $subject->getKey()
//            $query->orWhere(function($q) use ($user,$subject){
//                $q->where('subject_id','<>',$subject->getKey());
//                $q->whereIn('subject_id', function ($query) use ($user) {
//                    $user->subjectsIncludingShared($query)->select('id');
//                });
//            });
        }

        return $query;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();
        $query = $this->differentScenariosAndDemo($query,$filters);
        $joins = [];

        // Have to apply search filter first due to subquery left join with parameters
        if (array_key_exists('search', $filters)) {
            $value = $filters['search'];

            // Decide whenever open question table has to be searched/joined
            if (array_key_exists('type', $filters)) {
                if (is_array($filters['type'])) {
                    $types = array_map('strtolower', $filters['type']);
                } else {
                    $types = strtolower($filters['type']);
                }

                if ((is_array($types) && in_array('openquestion', $types) && count($types) === 1) || (!is_array($types) && $types == 'openquestion')) {
                    $openQuestionOnly = true;
                } else {
                    $openQuestionOnly = false;
                }

                if ((is_array($types) && !in_array('openquestion', $types)) || (!is_array($types) && $types !== 'openquestion')) {
                    $openQuestionDisabled = true;
                } else {
                    $openQuestionDisabled = false;
                }
            } else {
                $openQuestionOnly = false;
                $openQuestionDisabled = false;
            }

            if (!$openQuestionDisabled) {
                $openQuestion = new OpenQuestion();
            } else {
                $openQuestion = null;
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            // Join tags
            $tags = Tag::whereIn('name', $value)->pluck('name', 'id')->all();
            if ($tags) {
                $tags = array_map('strtolower', $tags);
                $subQuery = TagRelation::where('tag_relation_type', '=','tcCore\Question')
                    ->whereIn('tag_id', array_keys($tags))
                    ->select([
                        'tag_relation_id',
                        DB::raw('CONCAT(\' \', GROUP_CONCAT(tag_id SEPARATOR \' \'), \' \') as tags')
                    ])
                    ->groupBy('tag_relation_id');

                $query->leftJoinSub($subQuery, 'tags', function($join) {
                    $join->on('tags.tag_relation_id', '=', $this->getTable() . '.' . $this->getKeyName());
                });
            }

            // Search terms + tags
            foreach ($value as $v) {
                if(!in_array(strtolower($v), $tags)) {
                    $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion) {
                        $query->where('question', 'LIKE', '%' . $v . '%');
                        if (!$openQuestionDisabled) {
                            $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                        }
                        $query->orWhere('group_questions.name', 'like', '%' . $v . '%');
                    });
                } else {
                    $tagId = array_search(strtolower($v), $tags);
                    $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion, $tagId) {
                        $query->where('question', 'LIKE', '%' . $v . '%');
                        if (!$openQuestionDisabled) {
                            $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                        }
                        $query->orWhere(DB::raw('IFNULL(tags.tags, \'\')'), 'LIKE', '% ' . $tagId . ' %');
                        $query->orWhere('group_questions.name', 'like', '%' . $v . '%');
                    });
                }
            }

            if (!$openQuestionOnly && !array_key_exists('subtype', $filters) && !$openQuestionDisabled) {
                $query->leftJoin($openQuestion->getTable(), $openQuestion->getTable() . '.' . $openQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
            } elseif ($openQuestionOnly && !array_key_exists('subtype', $filters)) {
                $joins[] = 'openquestion';
            }
            $joins[] = 'groupquestion';
        }

        foreach($filters as $key => $value) {
            switch($key) {
                case 'base_subject_id':

                    if(isset($filters['source'])){
                        switch($filters['source']){
                            case 'schoolLocation': // only my colleages and me
                                if(is_array($value)) {
                                    $subjectIds = $user->subjects()->whereIn('base_subject_id', $value);
                                } else {
                                    $subjectIds = $user->subjects()->where('base_subject_id','=',$value);
                                }
                                $subjectIds = $subjectIds->pluck('id');
                                $query->whereIn('subject_id',$subjectIds);
                                break;
                            case 'school': //  shared sections
                                if(is_array($value)) {
                                    $subjectIds = $user->subjectsOnlyShared()->whereIn('base_subject_id', $value);
                                } else {
                                    $subjectIds = $user->subjectsOnlyShared()->where('base_subject_id','=',$value);
                                }
                                $subjectIds = $subjectIds->pluck('id');
                                $query->whereIn('subject_id',$subjectIds);
                                break;
                            default:
                                if(is_array($value)) {
                                    $subjectIds = $user->subjectsIncludingShared()->whereIn('base_subject_id', $value);
                                } else {
                                    $subjectIds = $user->subjectsIncludingShared()->where('base_subject_id','=',$value);
                                }
                                $subjectIds = $subjectIds->pluck('id');
                                $query->whereIn('subject_id',$subjectIds);
                                break;
                        }
                    } else {
                        if(is_array($value)) {
                            $subjectIds = $user->subjectsIncludingShared()->whereIn('base_subject_id', $value);
                        } else {
                            $subjectIds = $user->subjectsIncludingShared()->where('base_subject_id','=',$value);
                        }
                        $subjectIds = $subjectIds->pluck('id');
                        $query->whereIn('subject_id',$subjectIds);
                    }

                    break;
                case 'source':
                    if(isset($filters['base_subject_id'])){
                        // we don't have to do anything, cause here above already caught;
                    } else {
                        switch($filters['source']){
                            case 'me': // i need to be the author
                                $query->join('question_authors','questions.id','=','question_authors.question_id')
                                    ->where('question_authors.user_id','=',$user->getKey());
                                break;
                            case 'schoolLocation': // only my colleages and me
                                $query->whereIn('subject_id',$user->subjects()->pluck('id'));
                                $query->where($this->table.'.owner_id', Auth::user()->school_location_id);
                                break;
                            case 'school': //  shared sections
                                $query->whereIn('subject_id',$user->subjectsOnlyShared()->pluck('id'));
                                break;
                            default:
                                $query->whereIn('subject_id',$user->subjectsIncludingShared()->pluck('id'));
                                break;
                        }
                    }
                    break;
                case 'id':
                    if (is_array($value)) {
                        $query->whereIn($this->table.'.id', $value);
                    } else {
                        $query->where($this->table.'.id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    if (is_array($value)) {
                        $query->whereIn('subject_id', $value);
                    } else {
                        $query->where('subject_id', '=', $value);
                    }
                    break;
                case 'education_level_id':
                    if (is_array($value)) {
                        $query->whereIn('education_level_id', $value);
                    } else {
                        $query->where('education_level_id', '=', $value);
                    }
                    break;
                case 'education_level_year':
                    if (is_array($value)) {
                        $query->whereIn('education_level_year', $value);
                    } else {
                        $query->where('education_level_year', '=', $value);
                    }
                    break;
                case 'type':
                    if (is_array($value)) {
                        $filters['type'] = array_map('strtolower', $filters['type']);
                        $query->whereIn('type', $value);
                    } else {
                        $filters['type'] = strtolower($filters['type']);
                        $query->where('type', '=', $value);
                    }
                    break;
                case 'subtype':
                    $joinTable = null;
                    if (is_array($filters['type']) && in_array($filters['type'], array('matchingquestion', 'multiplechoicequestion', 'completionquestion', 'openquestion'))) {
                        break;
                    }

                    switch(strtolower($filters['type'])) {
                        case 'matchingquestion':
                        case 'multiplechoicequestion':
                        case 'completionquestion':
                        case 'openquestion':
                            $joinTable = $filters['type'];
                            break;
                    }

                    if ($joinTable !== null) {
                        $joins[] = $joinTable;
                    } else {
                        break;
                    }

                    if (is_array($value)) {
                        $query->whereIn('subtype', $value);
                    }elseif(strtolower($value) == 'long'){
                        $query->where('subtype', '=', 'long')->orWhere('subtype', '=', 'medium');
                    } else {
                        $query->where('subtype', '=', $value);
                    }
                    break;
                case 'question':
                    $query->where('question', 'LIKE', '%'.$value.'%');
                    break;
                case 'add_to_database':
                    $query->where('add_to_database', '=', $value);
                    break;
                case 'is_subquestion':
                    $query->where('is_subquestion', '=', $value);
                    break;
                case 'without_groups':
                    $query->where('type', '!=', 'GroupQuestion');
                    break;
                case 'author_id':
                   if (is_array($value)) {
                       $query->join('question_authors','questions.id','=','question_authors.question_id')
                           ->whereIn('question_authors.user_id',$value);
                    } else {
                       $query->join('question_authors','questions.id','=','question_authors.question_id')
                           ->where('question_authors.user_id','=',$value);
                    }
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'type':
                case 'question':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch(strtolower($key)) {
                case 'id':
                case 'type':
                case 'question':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        $joins = array_unique($joins);
        foreach($joins as $target) {
            switch (strtolower($target)) {
                case 'tests':
                    $test = new Test();
                    $query->join($test->getTable(), $test->getTable() . '.' . $test->getKeyName(), '=', $this->getTable() . '.test_id');
                    break;
                case 'matchingquestion':
                    $matchingQuestion = new MatchingQuestion();
                    $query->join($matchingQuestion->getTable(), $matchingQuestion->getTable() . '.' . $matchingQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'multiplechoicequestion':
                    $multipleChoiceQuestion = new MultipleChoiceQuestion();
                    $query->join($multipleChoiceQuestion->getTable(), $multipleChoiceQuestion->getTable() . '.' . $multipleChoiceQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'completionquestion':
                    $completionQuestion = new CompletionQuestion();
                    $query->join($completionQuestion->getTable(), $completionQuestion->getTable() . '.' . $completionQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'openquestion':
                    $openQuestion = new OpenQuestion();
                    $query->join($openQuestion->getTable(), $openQuestion->getTable() . '.' . $openQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
                case 'groupquestion':
                    $groupQuestion = new GroupQuestion();
                    $query->join($groupQuestion->getTable(), $groupQuestion->getTable() . '.' . $groupQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
                    break;
            }
        }

        return $query;
    }

    public function performReorder($entities, $movedEntity, $attribute) {
        $order = $movedEntity->getAttribute($attribute);
        $movedPrimaryKey = $movedEntity->getKey();

        $i = 1;
        if ($order) {

            foreach($entities as $entity) {
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
            foreach($entities as $entity) {
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

    public static function findByUuid($uuid) {

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

    public function deleteAnswers(){}

    /**
     * @param $mainQuestion either TestQuestion or GroupQuestionQuestion
     * @param $answers
     * @return array
     * @throws \Exception
     */
    public function addAnswers($mainQuestion, $answers){}

    private function convertMatchingAnswers($answers){
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            if($answer['left']==''){
                continue;
            }
            $this->addReturnArrayItemMatching($answer['left'],'LEFT',$returnArray);
            $this->addReturnArrayItemMatching($answer['right'],'RIGHT',$returnArray);
        }
        return $returnArray;
    }

    private function convertMatchingAnswersFromQuestion($question){
        $returnArray = [];
        $answers = $question->matchingQuestionAnswers->toArray();
        foreach ($answers as $key => $answer) {
            $returnArray[] = [ 'answer' => $answer['answer'],
                                'type' => $answer['type'],
                            ];
        }
        return $returnArray;
    }

    protected function trimAnswerOptions($answers){
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            if(!array_key_exists('answer',$answer)){
                $returnArray[] = $answer;
                continue;
            }
            if($answer['answer']==''){
                continue;
            }
            $returnArray[] = $answer;
        }
        return $returnArray;
    }


    private function convertRankingAnswersFromQuestion($question){
        $answers = $question->rankingQuestionAnswers->toArray();
        return $this->convertAnswersFromQuestion($answers,['order','answer']);
    }

    private function convertMultipleChoiceAnswersFromQuestion($question){
        $answers = $question->multipleChoiceQuestionAnswers->toArray();
        $ignoreOrder = false;
        if($question->subtype=='TrueFalse'){
            $ignoreOrder = true;
        }
        return $this->convertAnswersFromQuestion($answers,['order','answer','score'],$ignoreOrder);
    }

    private function convertAnswersFromQuestion($answers,$params,$ignoreOrder = false){
        $returnArray = [];
        foreach ($answers as $key => $answer) {
            $item = [];
            foreach ($params as $param) {
                if($param=='order'&&$ignoreOrder){
                    $item['order'] = 0;
                    continue;
                }
                if($param=='order'){
                    $item['order'] = ($key+1);
                    continue;
                }
                if(!array_key_exists($param, $answer)){
                    throw new Exception('unknown answer key');
                    continue;
                }
                $item[$param] = $answer[$param];
            }
            $returnArray[] = $item;
        }
        return $returnArray;
    }

    private function addReturnArrayItemMatching($answer,$type,&$returnArray):void
    {
        $answers = explode("\n", str_replace(["\r\n","\n\r","\r"],"\n",$answer) );
        foreach ($answers as $answerPart) {
            $returnArray[] = [ 'answer' => $answerPart,
                                'type' => $type,
                            ];
        }

    }


    public function getQuestionHtml()
    {
        return $this->getQuestionInstance()->question;
    }

    public function getCaptionAttribute()
    {
        return __('test_take.'.Str::snake($this->type));;
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
        return $groupQuestions->first()->groupQuestion->getKey();
    }

    public function getTotalDataForTestQuestionUpdate($request)
    {
        return $request->all();
    }

    public function updateWithRequest($request,$testQuestion)
    {
        $this->fill($this->getTotalDataForTestQuestionUpdate($request));
        $this->handleOnlyAddToDatabaseFieldIsModified($request);
        $this->handleAnyOtherFieldsAreModified($testQuestion,$request);
        return $this;
    }

    public function updateWithRequestGroup($request,$groupQuestionPivot,$groupQuestionQuestionManager)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        $this->fill($totalData);
        $this->handleOnlyAddToDatabaseFieldIsModified($request);
        $this->handleAnyOtherFieldsAreModifiedWithinGroupQuestion($request,$groupQuestionPivot,$groupQuestionQuestionManager);

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

    public function handleAnyOtherFieldsAreModified(TestQuestion $testQuestion,$request)
    {
        if(!$this->needsToBeUpdated($request)){
           return;
        }
        if ($this->isUsed($testQuestion)) {
            $this->handleDuplication($request);
            return;
        }
        $this->saveBothBaseModelAndQuestion();
    }

    public function handleAnyOtherFieldsAreModifiedWithinGroupQuestion($request,$groupQuestionPivot,$groupQuestionQuestionManager)
    {
        if(!$this->needsToBeUpdated($request)){
            return;
        }
        if($this->isUsedInGroupQuestion($groupQuestionQuestionManager,$groupQuestionPivot)){
            $this->handleDuplication($request);
            return;
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

    public function getKeyAfterPossibleDuplicate()
    {
        if(!$this->duplicateQuestionKey){
            return $this->getKey();
        }
        return $this->duplicateQuestionKey;
    }

    public function flushDuplicateQuestionKey()
    {
        $this->duplicateQuestionKey = false;
    }

    public function handleAnswersAfterOwnerModelUpdate($ownerModel,$request){
        $baseModel = $this->getQuestionInstance();
        if(!self::usesDeleteAndAddAnswersMethods($baseModel->type)){
            return;
        }
        if(!$this->needsToBeUpdated($request)){
            return;
        }
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        if(!array_key_exists('answers',$totalData)){
            return;
        }
        $this->deleteAnswers($this);
        $this->addAnswers($ownerModel,$totalData['answers']);
    }

    protected function handleDuplication($request)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        $question = $this->duplicate($totalData);
        if ($question === false) {
            throw new QuestionException('Failed to duplicate question');
        }
        $this->duplicateQuestionKey = $question->getKey();
        $this->addQuestionToAuthor($question);
    }

    public function onlyAddToDatabaseFieldNeedsToBeUpdated($request)
    {
        $baseModel = $this->getQuestionInstance($request);
        if(!$this->needsToBeUpdated($request) ){
            return false;
        }
        if(count($this->getDirty()) > 0){
            return false;
        }
        if(!array_key_exists('add_to_database', $baseModel->getDirty())){
            return false;
        }
        if(count($baseModel->getDirty()) === 1){
            return true;
        }
        return false;
    }

    public function needsToBeUpdated($request)
    {
        if($this->onlyAddToDatabaseFieldIsModified){
            return false;
        }
        if($this->isDirty()){
            return true;
        }
        $baseModel = $this->getQuestionInstance();
        if($baseModel->isDirty()){
            return true;
        }
        if($baseModel->isDirtyAttainments()){
            return true;
        }
        if($baseModel->isDirtyLearningGoals()){
            return true;
        }
        if($baseModel->isDirtyTags()){
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

    public function handleGroupDuplication($request,$groupQuestionQuestionManager,$groupQuestionPivot)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        $this->fill($totalData);
        if(!$this->isUsedInGroupQuestion($groupQuestionQuestionManager,$groupQuestionPivot)){
             return;
        }
        if(!$this->needsToBeUpdated($request)){
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
        if($this->groupQuestionPivot){
            return $this->groupQuestionPivot;
        }
        return $groupQuestionPivot;
    }

    public function convertInlineImageSources()
    {
        $questionHtmlConverter = new QuestionHtmlConverter($this->getQuestionHtml());

        return $questionHtmlConverter->convertImageSourcesWithPatternToNamedRoute('inline-image',self::INLINE_IMAGE_PATTERN);
    }

    public function getConvertedQuestionHtmlAttribute()
    {
        return $this->convertInlineImageSources();
    }

    public function getQuestionAttainmentsAsArray()
    {
        return $this->questionAttainments->map(function($relation) {
            return $relation->attainment_id;
        })->toArray();
    }

    public function getQuestionLearningGoalsAsArray()
    {
        return $this->questionLearningGoals->map(function($relation) {
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

    private function getQueryGetItemsFromSectionWithinSchoolLocation($user,$demoSubject)
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



    private function getQueryGetItemsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user,$demoSubject)
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
        return strip_tags(html_entity_decode($this->getQuestionHtml()));
    }

    public function getTypeNameAttribute()
    {
        return __('question.'.Str::lower($this->type.($this->subtype ?? '')));
    }

    public function getAuthorNamesString(): string
    {
        return $this->getAuthorNamesCollection()->implode(', ');
    }

    public function getAuthorNamesCollection()
    {
        return $this->authors()->get(['id', 'name', 'name_first', 'name_suffix'])->map(function($author) {
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

//        $test->load('testQuestions:id,question_id', 'testQuestions.question:id,derived_question_id');
//        return $test->testQuestions->filter(function($testQuestion) {
//            return $testQuestion->question->id === $this->getKey() || $testQuestion->question->derived_question_id === $this->getKey();
//        })->isNotEmpty();
    }

    public function hasCmsPreview()
    {
        return !$this->isType('matrix');
    }
}
