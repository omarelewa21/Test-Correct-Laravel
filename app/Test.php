<?php namespace tcCore;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\RequestController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Jobs\CountTeacherTests;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Question\QuestionGatherer;
use Dyrynda\Database\Casts\EfficientUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\ExamSchoolTestTrait;
use tcCore\Traits\UuidTrait;


class Test extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;
    use ExamSchoolTestTrait;


    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

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
    protected $table = 'tests';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subject_id', 'education_level_id', 'period_id', 'test_kind_id', 'name', 'abbreviation', 'education_level_year', 'kind', 'status', 'introduction', 'shuffle', 'is_open_source_content', 'demo', 'metadata', 'external_id', 'scope', 'published'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $sortableColumns = ['id', 'name', 'abbreviation', 'subject', 'education_level', 'education_level_year', 'period_id', 'test_kind_id', 'status', 'author', 'question_count', 'kind'];

    public static function boot()
    {
        parent::boot();

        static::created(function (Test $test) {
            TestAuthor::addAuthorToTest($test, $test->author_id);
            Queue::push(new CountTeacherTests($test->author));
        });

        static::saving(function (Test $test) {
            $dirty = $test->getDirty();
            if ((count($dirty) > 1 && array_key_exists('system_test_id', $dirty)) || (count($dirty) > 0 && !array_key_exists('system_test_id', $dirty)) && !$test->getAttribute('is_system_test')) {
                $test->setAttribute('system_test_id', null);
            }
            $test->handleExamPublishingTest();
        });

        static::saved(function (Test $test){
            $dirty = $test->getDirty();
            if( $test->isDirty(['subject_id','education_level_id','education_level_year'])){
                $testQuestions = $test->testQuestions;
                foreach ($testQuestions as $testQuestion){
                    if((    $testQuestion->question->subject_id==$test->subject_id)&&
                            ($testQuestion->question->education_level_id==$test->education_level_id)&&
                            ($testQuestion->question->education_level_year==$test->education_level_year)
                    ){
                        continue;
                    }
                    $request  = new Request();
                    $params = [
                        'session_hash' => Auth::user()->session_hash,
                        'user'         => Auth::user()->username,
                        'id' => $testQuestion->id,
                        'subject_id' => $test->subject_id,
                        'education_level_id' => $test->education_level_id,
                        'education_level_year' => $test->education_level_year
                    ];
                    $testQuestionQuestionId = $testQuestion->question->id;
                    $request->merge($params);
                    $response = (new TestQuestionsController())->updateFromWithin($testQuestion,  $request);
                    if($testQuestion->question->type=='GroupQuestion'){
                        $testQuestion = $testQuestion->fresh();
                        $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($testQuestion->uuid);
                        foreach($testQuestion->question->groupQuestionQuestions as $groupQuestionQuestion){
                            $request  = new Request();
                            $request->merge($params);
                            $response = (new GroupQuestionQuestionsController())->updateFromWithin($groupQuestionQuestionManager,$groupQuestionQuestion,  $request);
                        }
                    }
                }
            }
            $test->handleExamPublishingQuestionsOfTest();
            TestAuthor::addExamAuthorToTest($test);
        });

        static::deleted(function (Test $test) {
            Queue::push(new CountTeacherTests($test->author));
        });
    }

    public function owner()
    {
        return $this->belongsTo(SchoolLocation::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany('tcCore\Tag', 'tag_relation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testTakes()
    {
        return $this->hasMany('tcCore\TestTake');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function systemTest()
    {
        if ($this->getAttribute('is_system_test')) {
            return $this->belongsTo('tcCore\Test', 'system_test_id');
        } else {
            return $this->hasMany('tcCore\Test', 'system_test_id');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author()
    {
        return $this->belongsTo('tcCore\User')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testQuestions()
    {
        return $this->hasMany('tcCore\TestQuestion', 'test_id');
    }

    public function testAuthors()
    {
        return $this->hasMany(TestAuthor::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subject()
    {
        return $this->belongsTo('tcCore\Subject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function educationLevel()
    {
        return $this->belongsTo('tcCore\EducationLevel');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function testKind()
    {
        return $this->belongsTo('tcCore\TestKind');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function period()
    {
        return $this->belongsTo('tcCore\Period');
    }

    public function reorder($movedTestQuestion = null)
    {
        if ($movedTestQuestion !== null) {
            $order = $movedTestQuestion->getAttribute('order');
        }

        $testQuestions = $this->testQuestions()->orderBy('order')->get();

        $i = 1;
        if ($movedTestQuestion !== null && $order) {
            foreach ($testQuestions as $testQuestion) {
                if ($testQuestion->getKey() === $movedTestQuestion->getKey()) {
                    continue;
                }

                if ($i == $order) {
                    $i++;
                }

                $doCallbacks = $testQuestion->doCallbacks();
                $testQuestion->setCallbacks(false);
                $testQuestion->setAttribute('order', $i);
                $testQuestion->save();
                $testQuestion->setCallbacks($doCallbacks);
                $i++;
            }

            if ($i < $order) {
                $doCallbacks = $movedTestQuestion->doCallbacks();
                $movedTestQuestion->setCallbacks(false);
                $movedTestQuestion->setAttribute('order', $i);
                $movedTestQuestion->save();
                $movedTestQuestion->setCallbacks($doCallbacks);
            }
        } else {
            foreach ($testQuestions as $testQuestion) {
                if ($movedTestQuestion !== null && $testQuestion->getKey() === $movedTestQuestion->getKey()) {
                    continue;
                }

                $doCallbacks = $testQuestion->doCallbacks();
                $testQuestion->doCallbacks(false);
                $testQuestion->setAttribute('order', $i);
                $testQuestion->save();
                $testQuestion->doCallbacks($doCallbacks);
                $i++;
            }

            if ($movedTestQuestion !== null) {
                $doCallbacks = $movedTestQuestion->doCallbacks();
                $movedTestQuestion->setCallbacks(false);
                $movedTestQuestion->setAttribute('order', $i);
                $movedTestQuestion->save();
                $movedTestQuestion->setCallbacks($doCallbacks);
            }
        }
    }

    public function scopeCitoFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();

        $citoSchool = SchoolLocation::where('customer_code', 'CITO-TOETSENOPMAAT')->first();
        $baseSubjectIds = $user->subjects()->pluck('base_subject_id')->unique();

        $query->select();

        if ($citoSchool) {
            $classIds = $citoSchool->schoolClasses()->pluck('id');
            $tempSubjectIds = Teacher::whereIn('class_id', $classIds)->pluck('subject_id')->unique();
            $baseSubjects = Subject::whereIn('id', $tempSubjectIds)->get();
//            $baseSubjectIds = collect($baseSubjectIds);
            $subjectIds = $baseSubjects->whereIn('base_subject_id', $baseSubjectIds)->pluck('id')->unique()->toArray();
        } else { // slower but as a fallback in case there's no cito school
            $query->where('tests.id', -1);
            return $query;
        }

        $query->whereIn('subject_id', $subjectIds);
        $query->where('scope', 'cito');
        $query->where(function ($q) use ($user) {
            return $q->where('published', true)
                ->orWhere('author_id', $user->getKey());
        });

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'nameOrAbbreviation':
                    $query->where(function ($query) use ($value) {
                        $query->where('name', 'LIKE', '%' . $value . '%')->orWhere('abbreviation', 'LIKE', '%' . $value . '%');
                    });
                    break;
                case 'name':
                    $query->where('name', 'LIKE', '%' . $value . '%');
                    break;
                case 'abbreviation':
                    $query->where('abbreviation', 'LIKE', '%' . $value . '%');
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
                    $query->where('education_level_year', '=', $value);
                    break;
                case 'period_id':
                    if (is_array($value)) {
                        $query->whereIn('period_id', $value);
                    } else {
                        $query->where('period_id', '=', $value);
                    }
                    break;
                case 'test_kind_id':
                    if (is_array($value)) {
                        $query->whereIn('test_kind_id', $value);
                    } else {
                        $query->where('test_kind_id', '=', $value);
                    }
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'created_at_start':
                    $query->where('created_at', '>=', $value);
                    break;
                case 'created_at_end':
                    $query->where('created_at', '<=', $value);
                    break;
                case 'is_system_test':
                    $query->where('is_system_test', '=', $value);
                    break;
                case 'author_id':
                    if (is_array($value)) {
                        $query->whereIn('author_id', $value);
                    } else {
                        $query->where('author_id', '=', $value);
                    }
                    break;
            }
        }


        $this->handleFilteredSorting($query, $sorting);

        if ($user->isA('teacher')) {
            // don't show demo tests from other teachers
            $query->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('demo', 1)
                        ->where('author_id', $user->getKey());
                })
                    ->orWhere('demo', 0);
            });
        }

        return $query;
    }

    public function scopeExamFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();
        $query->select();
        $subjectIds = Subject::getSubjectsOfCustomSchoolForUser(config('custom.examschool_customercode'),$user);
        if(count($subjectIds)==0){
            $query->where('tests.id', -1);
            return $query;
        }
        $query->whereIn('subject_id', $subjectIds);
        $query->where('scope', 'exam');
        $query->where('published', true);
        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }
        $this->handleFilterParams($query,$filters);
        $this->handleFilteredSorting($query, $sorting);

        return $query;
    }

    public function scopeSharedSectionsFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();

        $sharedSectionIds = $user->schoolLocation->sharedSections()->pluck('id')->unique();
        $baseSubjectIds = $user->subjects()->pluck('base_subject_id')->unique();
        $subjectIds = [];

        $query->select();

        if (count($sharedSectionIds) > 0) {
            $subjectIds = Subject::whereIn('section_id', $sharedSectionIds)->whereIn('base_subject_id',$baseSubjectIds)->pluck('id')->unique();
        } else {
            $query->where('tests.id', -1);
            return $query;
        }

        $query->whereIn('subject_id', $subjectIds);
        $query->where('published', true);

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        $this->handleFilterParams($query,$filters);
        $this->handleFilteredSorting($query, $sorting);

        // don't show demo tests from other location
        $query->where('demo', 0);

        return $query;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        DB::enableQueryLog();
        $user = Auth::user();

        $roles = $this->getUserRoles();
        $schoolLocation = SchoolLocation::find($user->getAttribute('school_location_id'));

        $query->select();

        if ($schoolLocation->is_allowed_to_view_open_source_content == 1) {
            // @TODO WHY IS THIS ONLY ON THE FIRST BASE SUBJECT????????
            $baseSubjectId = $user->subjects()->select('base_subject_id')->first();
            $subjectIds = BaseSubject::find($baseSubjectId['base_subject_id'])->subjects()->select('id')->get();

            $query->whereIn('subject_id', $subjectIds);

            if (isset($filters['is_open_sourced_content']) && $filters['is_open_sourced_content'] == 1) {
                $query->where('is_open_source_content', '=', 1);
            } else {

                if (!isset($filters['is_open_sourced_content'])) {
                    $opensource = 1;
                } else {
                    $opensource = 0;
                }

                $query->where('is_open_source_content', '=', $opensource)->orWhereIn('author_id', function ($query) use ($user) {
                    $query->select('user_id')
                        ->from(with(new Teacher())->getTable())
                        ->whereIn('subject_id', function ($query) use ($user) {
                            $query->select('id')
                                ->from(with(new Subject())->getTable())
                                ->whereIn('section_id', function ($query) use ($user) {
                                    $user->sections($query)->select('id');
                                });
                        });

                    $query->join($user->getTable(), with(new Teacher())->getTable() . '.user_id', '=', $user->getTable() . '.' . $user->getKeyName());

                    $schoolId = $user->getAttribute('school_id');
                    $schoolLocationId = $user->getAttribute('school_location_id');
                    if ($schoolId && $schoolLocationId) {
                        $query->where(function ($query) use ($schoolId, $schoolLocationId) {
                            $query->where('school_id', $schoolId)
                                ->orWhere('school_location_id', $schoolLocationId);
                        });
                    } elseif ($schoolId !== null) {
                        $query->where('school_id', $schoolId);
                    } elseif ($schoolLocationId !== null) {
                        $query->where('school_location_id', $schoolLocationId);
                    }
                });
            }

        } elseif (in_array('Teacher', $roles)) {
            $query->join($this->switchScopeFilteredSubQueryForDifferentScenarios($user), function ($join) {
                $join->on('tests.id', '=', 't1.t2_id');
            });



            $subject = (new DemoHelper())->getDemoSectionForSchoolLocation($user->getAttribute('school_location_id'));
            if(!is_null($subject)){
                $query->where(function ($q) use ($user,$subject) {
                    $q->where(function ($query) use ($user, $subject) {
                        $query->where('tests.subject_id', $subject->getKey())->where('tests.author_id', $user->getKey());
                    })->orWhere('tests.subject_id', '<>', $subject->getKey());
                });
            }

         }

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        $this->handleFilterParams($query,$filters);
        $this->handleFilteredSorting($query, $sorting);

        if ($user->isA('teacher')) {
            // don't show demo tests from other teachers
            $query->where(function ($query) use ($user) {
                $query->where(function ($query) use ($user) {
                    $query->where('tests.demo', 1)
                        ->where('author_id', $user->getKey());
                })
                    ->orWhere('tests.demo', 0);
            });
        }

        return $query;
    }

    private function switchScopeFilteredSubQueryForDifferentScenarios($user)
    {
        if ($user->hasSingleSchoolLocation()) {
            return $this->getSubQueryForScopeFilteredSingleSchoolLocation($user);
        }
        return $this->getSubQueryForScopeFilteredMultipleSchoolLocations($user);

//        if ($user->hasSingleSchoolLocationNoSharedSections()) {
//            return $this->getSubQueryForScopeFilteredSingleSchoolLocationNoSharedSections($user);
//        } elseif ($user->hasMultipleSchoolLocationsNoSharedSections()) {
//            return $this->getSubQueryForScopeFilteredMultipleSchoolLocationsNoSharedSections($user);
//        }elseif (   $user->hasSingleSchoolLocationSharedSections()){
//            return $this->getSubQueryForScopeFilteredSingleSchoolLocationSharedSections($user);
//        }
//        return $this->getSubQueryForScopeFilteredMultipleSchoolLocationsSharedSections($user);
    }




    public function allowChange()
    {
        return $this->getAttribute('is_system_test') != true;
    }

    public function processChange()
    {
        if ($this->getAttribute('system_test_id') !== null && !$this->getAttribute('is_system_test')) {
            $this->setAttribute('system_test_id', null);
            $this->save();
        }
    }

    public function performMetadata()
    {
        QuestionGatherer::invalidateTestCache($this);
//        $questionsCount = QuestionGatherer::getQuestionsCountOfTest($this->getKey());
        $this->setAttribute('question_count', $this->getQuestionCount());
        $this->save();
    }

    public function userDuplicate(array $attributes, $authorId = null)
    {
        if (!array_key_exists('name', $attributes)) {
            $copy = 1;
            $names = static::where('author_id', $authorId)->where('name', 'LIKE', 'Kopie #% ' . $this->getAttribute('name'))->pluck('name')->all();
            while (in_array('Kopie #' . $copy . ' ' . $this->getAttribute('name'), $names)) {
                $copy++;
            }
            $attributes['name'] = 'Kopie #' . $copy . ' ' . $this->getAttribute('name');
        }

        return $this->duplicate($attributes, $authorId);
    }

    public function duplicate(array $attributes, $authorId = null, callable $callable = null)
    {
        $test = $this->replicate();
        $test->fill($attributes);

        if ($authorId !== null) {
            $test->setAttribute('author_id', $authorId);
        }

        if ($callable !== null) {
            $callable($test);
        }

        if ($test->getAttribute('is_system_test') == true) {
            $test->setAttribute('is_system_test', 0);
            $isSystemTest = true;
        } else {
            $isSystemTest = false;
        }

        $test->setAttribute('uuid', Uuid::uuid4());

        if ($test->save() === false) {
            return false;
        }

        $this->load(['testQuestions' => function ($query) {
            $query->orderBy('order');
        }]);

        foreach ($this->testQuestions as $testQuestion) {
            if ($testQuestion->duplicate($test, [], false) === false) {
                return false;
            }
        }

        $test->reorder();

        $tags = $this->tags()->pluck('id')->all();
        if ($tags) {
            $test->tags()->attach($tags);
        }

        if ($isSystemTest) {
            $test->setAttribute('is_system_test', 1);
            $test->setAttribute('system_test_id', $this->getKey());
            $test->save();
        }

        // existing testauthors duplicate
        $this->testAuthors()->pluck('user_id')->each(function($userId) use ($test){
            TestAuthor::addAuthorToTest($test, $userId);
        });

        // add testauthor if author_id is not null
        if(null !== $authorId){
            TestAuthor::addAuthorToTest($test, $authorId);
        }

        $test->setAttribute('derived_test_id',$this->getKey());
        $test->save();

        return $test;
    }

    public function scopeNotDemo($query, $tableAlias = null)
    {
        if (!$tableAlias) {
            $tableAlias = $this->getTable();
        }

        return $query->where(sprintf('%s.demo', $tableAlias), 0);
    }

    public function getHasDuplicatesAttribute()
    {
        return !!DB::select('
            select (id)
                from (
                select
                  question_id as id
                  from
                  `test_questions`
                where
                  `test_id` = ?  and `deleted_at` is null
                Union  all
                  select question_id as id from group_question_questions where group_question_id in(
                  select question_id from test_questions where test_id = ? and deleted_at is null
                  ) and deleted_at is null


                )as t
                 group by
                  `id`

                having
                  COUNT(id) > 1
        ', [$this->getKey(), $this->getKey()]);

    }

    public function getQuestionCount()
    {
        $this->load(['testQuestions','testQuestions.question']);
        $questionCount = 0;
        foreach($this->testQuestions as $testQuestion) {
            if(null !== $testQuestion->question) {
                $questionCount += $testQuestion->question->getQuestionCount();
            }
        }
        return $questionCount;
    }

    private function handleFilteredSorting($query, $sorting)
    {
        $sortDirections = ['asc', 'desc'];

        collect($sorting)->each(function ($direction, $key) use ($query, $sortDirections) {
            if (!in_array(Str::lower($direction), $sortDirections)) {
                return;
            }
            if (!in_array($key, $this->sortableColumns)) {
                return;
            }

            if ($key === 'subject') {
                $query->orderBy(
                    Subject::select('name')
                        ->whereColumn('id', 'tests.subject_id')
                        ->orderBy('name', $direction)
                        ->take(1),
                    $direction
                );
                return;
            }
            if ($key === 'education_level') {
                $query->orderBy(
                    EducationLevel::select('name')
                        ->whereColumn('id', 'tests.education_level_id')
                        ->orderBy('name', $direction)
                        ->take(1),
                    $direction
                );
                return;
            }
            if ($key === 'author') {
                $query->orderBy(
                    User::select(DB::raw('TRIM(CONCAT_WS(" ", COALESCE(name_first,""), COALESCE(name_suffix,""), COALESCE(name,""))) AS author'))
                        ->whereColumn('id', 'tests.author_id')
                        ->withTrashed()
                        ->orderBy('author', $direction)
                        ->take(1),
                    $direction
                );
                return;
            }
            if ($key === 'kind') {
                $query->orderBy(
                    TestKind::select('name')
                        ->whereColumn('id', 'tests.test_kind_id')
                        ->orderBy('name', $direction)
                        ->take(1),
                    $direction
                );
                return;
            }

            $query->orderBy($key, $direction);
        });

        return $query;
    }

    private function handleFilterParams(&$query,$filters):void
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'nameOrAbbreviation':
                    $query->where(function ($query) use ($value) {
                        $query->where('tests.name', 'LIKE', '%' . $value . '%')->orWhere('abbreviation', 'LIKE', '%' . $value . '%');
                    });
                    break;
                case 'name':
                    $query->where('tests.name', 'LIKE', '%' . $value . '%');
                    break;
                case 'abbreviation':
                    $query->where('abbreviation', 'LIKE', '%' . $value . '%');
                    break;
                case 'subject_id':
                    if (is_array($value)) {
                        $query->whereIn('tests.subject_id', $value);
                    } else {
                        $query->where('tests.subject_id', '=', $value);
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
                case 'period_id':
                    if (is_array($value)) {
                        $query->whereIn('period_id', $value);
                    } else {
                        $query->where('period_id', '=', $value);
                    }
                    break;
                case 'test_kind_id':
                    if (is_array($value)) {
                        $query->whereIn('test_kind_id', $value);
                    } else {
                        $query->where('test_kind_id', '=', $value);
                    }
                    break;
                case 'status':
                    $query->where('status', $value);
                    break;
                case 'created_at_start':
                    $query->where('created_at', '>=', $value);
                    break;
                case 'created_at_end':
                    $query->where('created_at', '<=', $value);
                    break;
                case 'is_system_test':
                    $query->where('is_system_test', '=', $value);
                    break;
                case 'author_id':
                    if (is_array($value)) {
                        $query->whereIn('author_id', $value);
                    } else {
                        $query->where('author_id', '=', $value);
                    }
                    break;
            }
        }
    }

    private function getSubQueryForScopeFilteredSingleSchoolLocation($user)
    {
        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
            ' union '.
            $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
            ') as t1'
        );
    }

    private function getSubQueryForScopeFilteredMultipleSchoolLocations($user)
    {
        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
            ' union '.
            $this->getQueryGetTestsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user).
            ' union '.
            $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
            ' ) as t1'
        );
    }

//    private function getSubQueryForScopeFilteredSingleSchoolLocationNoSharedSections($user)
//    {
//        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
//                                 ' union '.
//                                 $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
//                                ') as t1'
//        );
//    }
//
//    private function getSubQueryForScopeFilteredMultipleSchoolLocationsNoSharedSections($user)
//    {
//        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
//                                 ' union '.
//                                $this->getQueryGetTestsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user).
//                                 ' union '.
//                                $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
//                                ' ) as t1'
//        );
//    }
//
//    private function getSubQueryForScopeFilteredSingleSchoolLocationSharedSections($user)
//    {
//        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
//                                ' union  '.
//                                $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
//                                ' union '.
//                                 $this->getQueryGetTestsFromSharedSectionsWhereUserHasAccess($user).
//                                ' ) as t1'
//        );
//    }
//
//    private function getSubQueryForScopeFilteredMultipleSchoolLocationsSharedSections($user)
//    {
//        return DB::raw('('.$this->getQueryGetTestsFromSchoolLocationAuthoredByUser($user).
//            ' union '.
//            $this->getQueryGetTestsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user).
//            ' union '.
//            $this->getQueryGetTestsFromSectionWithinSchoolLocation($user).
//            ' union '.
//            $this->getQueryGetTestsFromSharedSectionsWhereUserHasAccess($user).
//            ' ) as t1'
//        );
//
//    }

    public function hasOpenQuestion(){
        return !! collect(QuestionGatherer::getQuestionsOfTest($this->getKey(), true))->search(function(Question $question){
            return !$question->canCheckAnswer();
        });
    }

    private function getQueryGetTestsFromSchoolLocationAuthoredByUser($user)
    {
        return sprintf('select distinct t2.id as t2_id  /* select all tests from schoollocation authored by user */
                                from
                                   `tests` as t2
                                        left join test_authors
                                            on t2.id = test_authors.test_id
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
                                            where test_authors.user_id = %d',
                                            $user->school_location_id,
                                            $user->id);
    }

    private function getQueryGetTestsFromSectionWithinSchoolLocation($user)
    {
        return sprintf('select distinct t2.id as t2_id /* select tests from active schoollocation with subjects that fall under the section the user is member of */
                                            from
                                               `tests` as t2
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
                                                    on t2.subject_id = s2.subject_id
                                            where t2.demo = false',
                                                    $user->id,
                                                    $user->school_location_id
        );
    }



    private function getQueryGetTestsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user)
    {
        return sprintf('select distinct t2.id as t2_id  /* select tests from all schoollocations authored by user and currently taught in active schoollocation */
                                            from
                                               `tests` as t2
                                                    left join test_authors
                                                        on t2.id = test_authors.test_id
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
                                            where test_authors.user_id = %d and t2.demo = false',
                                            $user->id,
                                            $user->school_location_id,
                                            $user->id
        );
    }

    private function getQueryGetTestsFromSharedSectionsWhereUserHasAccess($user)
    {
        return sprintf('select distinct t2.id as t2_id  /* select tests from other schoollocations that fall under a shared section the user has access to  */
                                            from
                                               `tests` as t2
                                               inner join (
                                                        select distinct t11.id as subject_id
                                                            from school_location_shared_sections
                                                                inner join sections
                                                                    on school_location_shared_sections.section_id = sections.id
                                                                left join subjects as t11
                                                                    on sections.id = t11.section_id
                                                                inner join (
                                                                    select base_subject_id
                                                                        from teachers
                                                                            left join subjects
                                                                                on teachers.subject_id = subjects.id
                                                                            left join sections
                                                                                on subjects.section_id = sections.id
                                                                            left join school_location_sections
                                                                                on sections.id = school_location_sections.section_id
                                                                        where teachers.user_id = %d
                                                                                and
                                                                           teachers.deleted_at is null
                                                                                and
                                                                           school_location_sections.school_location_id = %d
                                                                ) as s3
                                                                    on t11.base_subject_id = s3.base_subject_id
                                                            ) as s2
                                                    on t2.subject_id = s2.subject_id
                                            where t2.demo = false',
                                                    $user->id,
                                                    $user->school_location_id
        );
    }

    public function isAssignment() {
        return $this->test_kind_id == TestKind::ASSESSMENT_TYPE;
    }

    public function getAuthorsAsStringAttribute()
    {
        return $this->testAuthors()->get()->map(function($author) {
            return implode(' ', array_filter([$author->user->name_first, $author->user->name_suffix, $author->user->name]));
        })->join(',');

    }

    public function getRelativeOrderNumberForQuestion($questionUuid = null)
    {
        $questions = $this->testQuestions->flatMap(function ($testQuestion) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) {
                    return $item->question;
                });
            }
            return collect([$testQuestion->question]);
        });

        if (!$questionUuid) {
            return $questions->count() + 1;
        }

        return $questions->search(function($question) use ($questionUuid) {
            return $question->uuid === $questionUuid;
        }) + 1;
    }

    public function getRelativeOrderNumberForSubQuestion($gqq = null)
    {
        $groupQuestionId = TestQuestion::whereUuid($gqq)->value('question_id');
        $questions = $this->testQuestions->flatMap(function ($testQuestion) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($testQuestion) {
                    $item->question->groupQuestionId = $testQuestion->question->id;
                    return $item->question;
                });
            }
            return collect([$testQuestion->question]);
        });

        $index = $questions->search(function($question) use ($groupQuestionId) {
            return $question->groupQuestionId === $groupQuestionId;
        });
        $counted = $questions->where('groupQuestionId', $groupQuestionId)->count();

        return $index + $counted + 1;
    }
}
