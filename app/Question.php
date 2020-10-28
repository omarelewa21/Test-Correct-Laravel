<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Models\MtiBaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class Question extends MtiBaseModel {
    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    public $mtiBaseClass = 'tcCore\Question';
    public $mtiClassField = 'type';
    public $mtiParentTable = 'questions';



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
    protected $fillable = ['subject_id', 'education_level_id', 'type', 'question', 'education_level_id', 'score', 'decimal_score', 'note_type', 'rtti', 'bloom','miller','add_to_database','is_open_source_content', 'metadata', 'external_id','scope','styling'];

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

    protected $tags = null;

    public static function usesDeleteAndAddAnswersMethods($questionType)
    {
        return collect(['completionquestion', 'matchingquestion', 'rankingquestion','matrixquestion'])->contains(strtolower($questionType));
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
        static::created(function(Question $question)
        {
            QuestionAuthor::addAuthorToQuestion($question);
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
        return $this->belongsToMany('tcCore\User', 'question_authors', 'question_id', 'user_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
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
        return $this->hasMany('tcCore\QuestionAttainment', 'question_id');
    }

    public function attainments() {
        return $this->belongsToMany('tcCore\Attainment', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function testTakes() {
        return $this->hasMany('tcCore\TestTake', 'discussing_question_id');
    }

    public function pValue() {
        return $this->hasMany('tcCore\PValue');
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
        return $this->isCitoQuestion();
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

    protected function saveAttainments() {
        $questionAttainments = $this->questionAttainments()->withTrashed()->get();
        $this->syncTcRelation($questionAttainments, $this->attainments, 'attainment_id', function($question, $attainmentId) {
            QuestionAttainment::create(['question_id' => $question->getKey(), 'attainment_id' => $attainmentId]);
        });

        $this->attainments = null;
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

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
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
                $subQuery = DB::table('tag_relations')->where('deleted_at', null)->where('tag_relation_type', 'tcCore\Question')->whereIn('tag_id', array_keys($tags))->select(['tag_relation_id', DB::raw('CONCAT(\' \', GROUP_CONCAT(tag_id SEPARATOR \' \'), \' \') as tags')])->groupBy('tag_relation_id');
                $query->leftJoin(DB::raw('(' . $subQuery->toSql() . ') as tags'), 'tags.tag_relation_id', '=', $this->getTable() . '.' . $this->getKeyName());
                $query->mergeBindings($subQuery);
            }

            // Search terms + tags
            foreach ($value as $v) {
                if(!in_array(strtolower($v), $tags)) {
                    $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion) {
                        $query->where('question', 'LIKE', '%' . $v . '%');
                        if (!$openQuestionDisabled) {
                            $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                        }
                    });
                } else {
                    $tagId = array_search(strtolower($v), $tags);
                    $query->where(function ($query) use ($v, $openQuestionDisabled, $openQuestion, $tagId) {
                        $query->where('question', 'LIKE', '%' . $v . '%');
                        if (!$openQuestionDisabled) {
                            $query->orWhere(DB::raw('IFNULL(' . $openQuestion->getTable() . '.answer, \'\')'), 'LIKE', '%' . $v . '%');
                        }
                        $query->orWhere(DB::raw('IFNULL(tags.tags, \'\')'), 'LIKE', '% ' . $tagId . ' %');
                    });
                }
            }

            if (!$openQuestionOnly && !array_key_exists('subtype', $filters) && !$openQuestionDisabled) {
                $query->leftJoin($openQuestion->getTable(), $openQuestion->getTable() . '.' . $openQuestion->getKeyName(), '=', $this->getTable() . '.' . $this->getKeyName());
            } elseif ($openQuestionOnly && !array_key_exists('subtype', $filters)) {
                $joins[] = 'openquestion';
            }
        }

        $roles = $this->getUserRoles();

        $user = Auth::user();
        $schoolLocation = SchoolLocation::find($user->getAttribute('school_location_id'));

        if($schoolLocation->is_allowed_to_view_open_source_content == 1) {

        	$baseSubjectId = $user->subjects()->select('base_subject_id')->first();
            $subjectIds = BaseSubject::find($baseSubjectId['base_subject_id'])->subjects()->select('id')->get();

         //    $query->whereIn('subject_id',$subjectIds);


	        if(!isset($filters['is_open_source_content']) || $filters['is_open_source_content'] == 0) {
				$query->whereIn('subject_id', function ($query) use ($user) {
	                $user->subjects($query)->select('id');
	            });

	        	$query->orWhere('is_open_source_content','=',1);

		    }elseif( $filters['is_open_source_content'] == 1 ) {
		    	$query->whereIn('subject_id', function ($query) use ($user) {
	                $user->subjects($query)->select('id');
	            });
		    }else{
		    	$query->whereIn('subject_id',$subjectIds);
		    	$query->where('is_open_source_content','=',1);
		    }

		} else {
			if (in_array('Teacher', $roles)) {
                $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
                $query->orWhere(function($q) use ($user, $subject){
                    // subject id = $subject->getKey() together with being an owner through the question_authors table
                    $q->where('subject_id',$subject->getKey());
                    $q->whereIn('questions.id',$user->questionAuthors()->pluck('question_id'));
                });
                // or subect_id in list AND subject not $subject->getKey()
                $query->orWhere(function($q) use ($user,$subject){
                    $q->where('subject_id','<>',$subject->getKey());
                    $q->whereIn('subject_id', function ($query) use ($user) {
                        $user->subjects($query)->select('id');
                    });
                });
	        }
		}

        foreach($filters as $key => $value) {
            switch($key) {
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
            switch(strtolower($target)) {
                case 'tests':
                    $test = new Test();
                    $query->join($test->getTable(), $test->getTable().'.'.$test->getKeyName(), '=', $this->getTable().'.test_id');
                    break;
                case 'matchingquestion':
                    $matchingQuestion = new MatchingQuestion();
                    $query->join($matchingQuestion->getTable(), $matchingQuestion->getTable().'.'.$matchingQuestion->getKeyName(), '=', $this->getTable().'.'.$this->getKeyName());
                    break;
                case 'multiplechoicequestion':
                    $multipleChoiceQuestion = new MultipleChoiceQuestion();
                    $query->join($multipleChoiceQuestion->getTable(), $multipleChoiceQuestion->getTable().'.'.$multipleChoiceQuestion->getKeyName(), '=', $this->getTable().'.'.$this->getKeyName());
                    break;
                case 'completionquestion':
                    $completionQuestion = new CompletionQuestion();
                    $query->join($completionQuestion->getTable(), $completionQuestion->getTable().'.'.$completionQuestion->getKeyName(), '=', $this->getTable().'.'.$this->getKeyName());
                    break;
                case 'openquestion':
                    $openQuestion = new OpenQuestion();
                    $query->join($openQuestion->getTable(), $openQuestion->getTable().'.'.$openQuestion->getKeyName(), '=', $this->getTable().'.'.$this->getKeyName());
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
}
