<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\ContentSourceHelper;
use tcCore\Jobs\CountTeacherTests;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Question\QuestionGatherer;
use Dyrynda\Database\Casts\EfficientUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Lib\Repositories\PValueRepository;
use tcCore\Lib\Repositories\TaxonomyRepository;
use tcCore\Services\ContentSource\ThiemeMeulenhoffService;
use tcCore\Traits\ModelAttributePurifyTrait;
use tcCore\Traits\PublishesTestsTrait;
use tcCore\Traits\UserPublishing;
use tcCore\Traits\UuidTrait;
use tcCore\Traits\UserContentAccessTrait;


class Test extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;
    use PublishesTestsTrait;
    use UserContentAccessTrait;
    use UserPublishing;
    use ModelAttributePurifyTrait;

    const NATIONAL_ITEMBANK_SCOPES = ['cito', 'exam', 'ldt'];

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'draft'      => 'boolean',
        'lang'       => WscLanguage::class,
        'deleted_at' => 'datetime',
    ];

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
    protected $fillable = ['subject_id', 'education_level_id', 'period_id', 'test_kind_id', 'name', 'abbreviation', 'education_level_year', 'kind', 'status', 'introduction', 'shuffle', 'is_open_source_content', 'demo', 'metadata', 'external_id', 'scope', 'published', 'draft'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $fieldsToDecodeOnRetrieval = ['name', 'abbreviation', 'introduction'];

    protected $sortableColumns = ['id', 'name', 'abbreviation', 'subject', 'education_level', 'education_level_year', 'period_id', 'test_kind_id', 'status', 'author', 'question_count', 'kind'];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Test $test) {
            $test->draft = true;
        });

        static::created(function (Test $test) {
            TestAuthor::addAuthorToTest($test, $test->author_id);
            Queue::push(new CountTeacherTests($test->author));
        });

        static::saving(function (Test $test) {
            $dirty = $test->getDirty();
            if ((count($dirty) > 1 && array_key_exists('system_test_id', $dirty)) || (count($dirty) > 0 && !array_key_exists('system_test_id', $dirty)) && !$test->getAttribute('is_system_test')) {
                $test->setAttribute('system_test_id', null);
            }
        });

        static::saved(function (Test $test) {
            $test->forwardPropertyChangesToDependentModels();
            $test->handleTestPublishing();
            $test->handlePublishingQuestionsOfTest();
            TestAuthor::addPublishAuthorToTest($test);
        });

        static::deleted(function (Test $test) {
            FileManagement::removeTestRelation($test);
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
        return $this->belongsTo('tcCore\Subject')->withTrashed();
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

    public function fileManagement()
    {
        return $this->hasOne(FileManagement::class);
    }

    public function reorder($movedTestQuestion = null)
    {
        if ($movedTestQuestion !== null) {
            $order = $movedTestQuestion->getAttribute('order');
        }

        $testQuestions = $this->testQuestions()->with('test')->orderBy('order')->get();

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

    public function contentSourceFiltered($scopes, $customer_codes, $query, User $forUser, $filters = [], $sorting = [])
    {
        $query->select();
        $subjectIds = Subject::getIdsForContentSource($forUser, Arr::wrap($customer_codes));
        if (is_array($subjectIds) && count($subjectIds) == 0) {
            $query->where('tests.id', -1);
            return $query;
        }

        $query->whereIn('subject_id', $subjectIds);
        $query->whereIn('scope', Arr::wrap($scopes));
        $query->published();

        $query->where(function ($q) use ($forUser) {
            return $q->where('published', true)
                ->orWhere('author_id', $forUser->getKey());
        });

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        $this->handleFilterParams($query, $filters);
        $this->handleFilteredSorting($query, $sorting);

        if ($forUser->isA('teacher')) {
            // don't show demo tests from other teachers
            $query->where(function ($query) use ($forUser) {
                $query->where(function ($query) use ($forUser) {
                    $query->where('demo', 1)
                        ->where('author_id', $forUser->getKey());
                })
                    ->orWhere('demo', 0);
            });
        }

        return $query;
    }
    /**
     * TODO this is used in app/Http/Controllers/Cito/TestsController.php:22
     * I think this is no longer used and can be removed september 5th 2023;
     * We use content labeled with scope exam but only as part of the NationalItemBank;
     */
    public function scopeCitoFiltered($query, $filters = [], $sorting = [])
    {
        Bugsnag::notifyException(new RuntimeException('Dead code marker detected please delete the marker the code is not dead.'), function ($report) {
            $report->setMetaData([
                'code_context' => [
                    'file' => __FILE__,
                    'class' => __CLASS__,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'timestamp' => date(DATE_ATOM),
                ]
            ]);
        });
        return $this->contentSourceFiltered(
            'cito',
            'CITO-TOETSENOPMAAT',
            auth()->user(),
            $query,
            $filters,
            $sorting,

        );
    }

    /**
     * TODO this is used in app/Http/Controllers/Exam/TestsController.php:22
     * I think this is no longer used and can be removed september 5th 2023;
     * We use content labeled with scope exam but only as part of the NationalItemBank;
     */
    public function scopeExamFiltered($query, $filters = [], $sorting = [])
    {
        Bugsnag::notifyException(new RuntimeException('Dead code marker detected please delete the marker the code is not dead.'), function ($report) {
            $report->setMetaData([
                'code_context' => [
                    'file' => __FILE__,
                    'class' => __CLASS__,
                    'method' => __METHOD__,
                    'line' => __LINE__,
                    'timestamp' => date(DATE_ATOM),
                ]
            ]);
        });
        return $this->contentSourceFiltered(
            'exam',
            config('custom.examschool_customercode'),
            auth()->user(),
            $query,
            $filters,
            $sorting
        );
    }


    public function scopeSharedSectionsFiltered( $query,User $forUser, $filters = [], $sorting = [])
    {
        $subjectIds = Subject::getIdsForSharedSections($forUser);
        if (!$subjectIds) {
            $query->where('tests.id', -1);
            return $query;
        }

        $query->whereIn('subject_id', $subjectIds);
        $query->where('published', true);
        $query->where('draft', false);

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        $this->handleFilterParams($query, $filters);
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

        if (in_array('Teacher', $roles)) {
            if ($user->isValidExamCoordinator() || $user->isToetsenbakker()) {
                $query->owner($user->schoolLocation);
            } else {
                $subject = (new DemoHelper())->getDemoSectionForSchoolLocation($user->getAttribute('school_location_id'));
                $query->join($this->switchScopeFilteredSubQueryForDifferentScenarios($user, $subject), function ($join) {
                    $join->on('tests.id', '=', 't1.t2_id');
                });

                if (!is_null($subject)) {
                    $query->where(function ($q) use ($user, $subject) {
                        $q->where(function ($query) use ($user, $subject) {
                            $query->where('tests.subject_id', $subject->getKey())->where('tests.author_id', $user->getKey());
                        })->orWhere('tests.subject_id', '<>', $subject->getKey());
                    });
                }
            }
        }

        if (!array_key_exists('is_system_test', $filters)) {
            $query->where('is_system_test', '=', 0);
        }

        $this->handleFilterParams($query, $filters);
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
            if(count($names)) {
                while (in_array('Kopie #' . $copy . ' ' . $this->getAttribute('name'), $names) && $copy < 100) {
                    $copy++;
                }
            }
            $attributes['name'] = 'Kopie #' . $copy . ' ' . $this->getAttribute('name');
        }

        $subjectId = false;
        if (isset($attributes['subject_id'])) {
            $subjectId = $attributes['subject_id'];
            unset($attributes['subject_id']);
        }

        $test = $this->duplicate($attributes, $authorId);

        if ($subjectId) {
            $test->refresh()->subject_id = $subjectId;
            $test->save();
        }
        return $test;
    }

    public function duplicate(array $attributes, $authorId = null, callable $callable = null)
    {
        $test = $this->replicate();
        $test->fill($attributes);
        if(ContentSourceHelper::getPublishableAbbreviations()->contains($test->abbreviation)) {
            $test->abbreviation = 'COPY';
        }

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

        $test->unsetRelation('testQuestions');

        if ($test->save() === false) {
            return false;
        }

        $this->load([
            'testQuestions' => fn($query) => $query->orderBy('order'),
            'testQuestions.question',
            'testQuestions.test'
        ]);

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
            $test->setAttribute('draft', $this->draft);
            $test->save();
        }

        // existing testauthors duplicate
        $this->testAuthors()->pluck('user_id')->each(function ($userId) use ($test) {
            $test->testAuthors()->withTrashed()->updateOrCreate(
                ['user_id' => $userId],
                ['deleted_at' => null]
            );
        });

        // add testauthor if author_id is not null
        if (null !== $authorId) {
            TestAuthor::addAuthorToTest($test, $authorId);
        }

        $test->setAttribute('derived_test_id', $this->getKey());
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
        return $this->getDuplicateQuestionIds()->isNotEmpty();
    }

    public function getTotalScore()
    {
        $this->load(['testQuestions', 'testQuestions.question']);
        $totalScore = 0;
        foreach ($this->testQuestions as $testQuestion) {
            if (null !== $testQuestion->question) {
                if($testQuestion->question->isType('GroupQuestion')){
                    $totalScore += $testQuestion->question->total_score ?? 0;
                } else {
                    $totalScore += $testQuestion->question->score ?? 0;
                }
            }
        }
        return $totalScore;
    }

    public function getQuestionCount()
    {
        $this->load(['testQuestions', 'testQuestions.question']);
        $questionCount = 0;
        foreach ($this->testQuestions as $testQuestion) {
            if (null !== $testQuestion->question) {
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

    private function handleFilterParams(&$query, $filters): void
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
                case 'base_subject_id':
                    if (is_array($value)) {
                        $query->whereIn('tests.subject_id', Subject::whereIn('base_subject_id', $value)->select('id'));
                    } else {
                        $query->whereIn('tests.subject_id', Subject::where('base_subject_id', $value)->select('id'));
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
                case 'owner_id':
                    $query->where('tests.owner_id', '=', $value);
                    break;
                case 'draft':
                    $query->where('tests.draft', '=', $value);
                    break;
                case 'taxonomy':
                    $taxonomyColumnsWithSearchValues = TaxonomyRepository::filterValuesPerTaxonomyGroup($value);

                    $query->whereIn(
                        'tests.id',
                        Question::selectRaw('test_questions.test_id')
                            ->join('test_questions', 'test_questions.question_id', '=', 'questions.id')
                            ->whereRaw('test_questions.test_id = tests.id')
                            ->taxonomies($taxonomyColumnsWithSearchValues)
                    );
                    break;
            }
        }
    }

    public function hasOpenQuestion()
    {
        return !!collect(QuestionGatherer::getQuestionsOfTest($this->getKey(), true))->search(function (Question $question) {
            return !$question->isClosedQuestion();
        });
    }

    public function getWritingAssignmentsCount()
    {
        return collect(QuestionGatherer::getQuestionsOfTest($this->getKey(), true))->filter(function (Question $question) {
            return $question->isWritingAssignment();
        })->count();
    }

    private function getQueryGetItemsFromSchoolLocationAuthoredByUser($user)
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
                                                /*subjects.deleted_at is null
                                                and*/
                                                t9.school_location_id = %d
                                                        ) as s2
                                                    on t2.subject_id = s2.subject_id
                                            where test_authors.user_id = %d',
            $user->school_location_id,
            $user->id);
    }

    private function getQueryGetItemsFromSectionWithinSchoolLocation($user, $demoSubject)
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
                                                            /*subjects.deleted_at is null
                                                                and*/
                                                            teachers.user_id = %d
                                                                and
                                                            teachers.deleted_at is null
                                                                and
                                                            t9.school_location_id = %d
                                                            ) as s2
                                                    on t2.subject_id = s2.subject_id
                                            where t2.demo = 0',
            $user->id,
            $user->school_location_id
        );
    }


    private function getQueryGetItemsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user, $demoSubject)
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
                                            where test_authors.user_id = %d and t2.demo = 0',
            $user->id,
            $user->school_location_id,
            $user->id
        );
    }


    public function isAssignment()
    {
        return $this->test_kind_id == TestKind::ASSIGNMENT_TYPE;
    }

    public function getAuthorsAsString()
    {
        return $this->authorsAsString;
    }

    public function getAuthorsAsStringAttribute()
    {
        return $this->getTestAuthorsWithMainAuthorFirst()->map(function ($author) {
            return implode(' ', array_filter([$author->user->name_first, $author->user->name_suffix, $author->user->name]));
        })->join(', ');
    }

    public function getAuthorsAsStringTwoAttribute()
    {
        $authorsToShow = 2;

        $names = $this->getTestAuthorsWithMainAuthorFirst()->map(function ($author) {
            return implode(' ', array_filter([$author->user->name_first, $author->user->name_suffix, $author->user->name]));
        });

        $return = $names->take($authorsToShow)->join(', ');


        if ($names->count() > $authorsToShow) {
            $return .= ' +' . ($names->count() - $authorsToShow);
        }

        return $return;
    }

    public function getTestAuthorsWithMainAuthorFirst()
    {
        $this->loadMissing(['testAuthors', 'testAuthors.user']);
        return $this->testAuthors
            ->sortByDesc(function ($author) {
                return $author->user_id === $this->author_id ? 1 : 0 ;
            })
            ->values();
    }

    public function getQuestionOrderList()
    {
        return $this->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) {
                    return $item->question->getKey();
                });
            }
            return [$testQuestion->question->getKey()];
        })->flip()->map(function ($orderNr) {
            return $orderNr + 1;
        })->toArray();
    }

    public function getQuestionOrderListWithDiscussionType()
    {
        $orderAllQuestion = 0;
        $orderInTest = 0;

        return $this->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions()->get()->map(function ($item) use ($testQuestion) {
                    return [
                        'id' => $item->question->getKey(),
                        'question_type' => $item->question->isClosedQuestion() ? Question::TYPE_CLOSED : Question::TYPE_OPEN,
                        'discuss'       => (!$testQuestion->question->isCarouselQuestion()) && $item->discuss ? 1 : 0,
                    ];
                });
            }
            $questionType = $testQuestion->question->isClosedQuestion() ? Question::TYPE_CLOSED : Question::TYPE_OPEN;
            return [['id' => $testQuestion->question->getKey(), 'question_type' => $questionType, 'discuss' => $testQuestion->discuss,]];
        })->mapWithKeys(function ($item, $key) use (&$orderOpenOnly, &$orderAllQuestion, &$orderInTest) {
            return [$item['id'] => [
                'order' => (bool)$item['discuss'] ? ++$orderAllQuestion : null,
                'order_in_test' => ++$orderInTest,
                ...$item,
            ]];
        })->toArray();
    }

    public function getQuestionOrderListExpanded($forgetCache = false)
    {
        $cacheKey = sprintf('test_questions_list_expanded-%s', $this->uuid);

        //Cache forget to forget the cache, when still in the set-up phase?
        if($forgetCache) {
            Cache::forget($cacheKey);
        }
        //Cache Remember for store/retrieve, when past the set-up phase?
        return Cache::remember($cacheKey, now()->addDays(3), function () {

            $coLearningIndex = 0; // filters out 'discuss === false' questions
            $testIndex = 0;

            $this->testQuestions->loadMissing('question');

            $orderList = $this->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
                if ($testQuestion->question->type === 'GroupQuestion') {
                    return $testQuestion->question->groupQuestionQuestions()->with('question')->get()->map(function ($item) use ($testQuestion) {
                        return [
                            'question_id' => $item->question->getKey(),
                            'question_uuid' => $item->question->uuid,
                            'question_type' => $item->question->type,
                            'question_type_name' => $item->question->type_name,
                            'question_title' => $item->question->title,
                            'group_question_id' => $testQuestion->question->getKey(),
                            'carousel_question' => $testQuestion->question->isCarouselQuestion(),
                            'open_question' => !$item->question->isClosedQuestion(),
                            //                        'discuss'       => (!$testQuestion->question->isCarouselQuestion()) && $item->discuss ? 1 : 0,
                        ];
                    });
                }
                return [[
                            'question_id' => $testQuestion->question->getKey(),
                            'question_uuid' => $testQuestion->question->uuid,
                            'question_type' => $testQuestion->question->type,
                            'question_type_name' => $testQuestion->question->type_name,
                            'question_title' => $testQuestion->question->title,
                            'group_question_id' => null,
                            'carousel_question' => false,
                            'open_question' => !$testQuestion->question->isClosedQuestion(),
                            //                'discuss' => $testQuestion->discuss,
                        ]];
            });

            $pValues = PValueRepository::getPValuesForQuestion($orderList->pluck('question_id')->toArray())
                ->keyBy('question_id');

            return $orderList->mapWithKeys(function ($item, $key) use (&$coLearningIndex, &$testIndex, &$pValues) {

                return [$item['question_id'] => [
                    'colearning_index' => ($item['question_type'] !== 'InfoscreenQuestion' && !$item['carousel_question']) ? ++$coLearningIndex : null,
                    'test_index' => ++$testIndex,
                    ...$item,
                    'p_value' => ($item['question_type'] !== 'InfoscreenQuestion' && !$item['carousel_question'])
                        ? $pValues->get($item['question_id'])?->p_value
                        : null,
                ]];
            })->toArray();
        });
    }

    public function canDuplicate()
    {
        return strtolower($this->scope) !== 'cito';
    }

    public function canEdit(User $user)
    {
        return $this->author->is($user);
    }

    public function canDelete(User $user)
    {
        return $this->author->is($user);
    }


    public function maxScore($ignoreQuestions = [])
    {
        if (is_null($ignoreQuestions)) {
            $ignoreQuestions = [];
        }
        $testId = $this->id;
        $maxScore = 0;
        $questions = QuestionGatherer::getQuestionsOfTest($testId, true);
        $carouselQuestions = QuestionGatherer::getCarouselQuestionsOfTest($testId);
        $carouselQuestionIds = array_map(function ($carouselQuestion) {
            return $carouselQuestion->getKey();
        }, $carouselQuestions);
        $carouselQuestionChilds = [];
        foreach ($questions as $key => $question) {
            if (!stristr($key, '.')) {
                $this->addToMaxScore($maxScore, $question, $ignoreQuestions);
                continue;
            }
            $arr = explode('.', $key);
            if (!in_array($arr[0], $carouselQuestionIds)) {
                $this->addToMaxScore($maxScore, $question, $ignoreQuestions);
                continue;
            }
            $carouselQuestionChilds[$arr[0]][$arr[1]] = $question;
        }
        foreach ($carouselQuestionChilds as $groupquestionId => $childArray) {
            if (in_array($groupquestionId, $ignoreQuestions)) {
                continue;
            }
            $questionScore = current($childArray)->score;
            $numberOfSubquestions = $carouselQuestions[$groupquestionId]->number_of_subquestions;
            $maxScore += ($questionScore * $numberOfSubquestions);
        }
        return $maxScore;
    }

    private function addToMaxScore(&$maxScore, $question, $ignoreQuestions): void
    {
        if (in_array($question->getKey(), $ignoreQuestions)) {
            return;
        }
        $maxScore += $question->score;
    }

    public function isCopy()
    {
        return false;
        if (!$this->created_at) {
            return false;
        }
        return $this->created_at->is($this->updated_at);
    }

    public function listOfTakeableTestQuestions()
    {
        return $this->testQuestions->sortBy('order')->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            $testQuestion->question->attachmentCount = $testQuestion->question->attachments()->count();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                $groupQuestion->subQuestions = $groupQuestion->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    $item->question->groupQuestionQuestionUuid = $item->uuid;
                    $item->question->attachmentCount = $item->question->attachments()->count();
                    return $item->question;
                });
            }
            return [$testQuestion];
        });
    }

    public function canCopy(User $user)
    {
        return $this->canDuplicate() && $user->school_location_id == $this->owner_id;
    }

    public function canCopyFromSchool(User $user)
    {
        if (!$this->owner->school) return false;

        return $this->canDuplicate() && $user->isAllowedSchool($this->owner->school);
    }

    public function getDuplicateQuestionIds()
    {
        $testQuestionsForTestQuery = TestQuestion::select('question_id as id')->whereTestId($this->getKey());
        $groupQuestionQuestionsFromTestQuestionsQuery = GroupQuestionQuestion::select('question_id as id')->whereIn('group_question_id', $testQuestionsForTestQuery);

        return DB::query()
            ->selectRaw('id')
            ->fromSub(
                $testQuestionsForTestQuery->unionAll($groupQuestionQuestionsFromTestQuestionsQuery),
                'duplicateIds'
            )
            ->groupBy('id')
            ->havingCount('id', '>', 1)
            ->pluck('id');
    }

    public function getAmountOfQuestions()
    {
        $groupQ = $this->testQuestions()
            ->select('id')
            ->withCount(['question' => function ($query) {
                $query->where('type', '=', 'GroupQuestion');
            }])
            ->get()
            ->sum(function ($testQuestion) {
                return $testQuestion->question_count;
            });

        return ['regular' => $this->getQuestionCount(), 'group' => $groupQ];
    }

    public static function findByUuid($uuid)
    {
        return self::whereUuid($uuid)->firstOrFail();
    }

    public function hasDuplicateQuestions()
    {
        return count($this->getDuplicateQuestionIds()) > 0;
    }

    public function hasTooFewQuestionsInCarousel()
    {
        $this->load(['testQuestions', 'testQuestions.question']);
        $countCarouselQuestionWithToFewQuestions = $this->testQuestions->filter(function ($testQuestion) {
            return ($testQuestion->question instanceof \tcCore\GroupQuestion && !$testQuestion->question->hasEnoughSubQuestionsAsCarousel());
        })->count();
        return $countCarouselQuestionWithToFewQuestions != 0;
    }

    public function hasNotEqualScoresForSubQuestionsInCarousel()
    {
        $this->load(['testQuestions', 'testQuestions.question']);
        $countCarouselQuestionWithToFewQuestions = $this->testQuestions->filter(function ($testQuestion) {
            return ($testQuestion->question instanceof \tcCore\GroupQuestion && $testQuestion->question->isCarouselQuestion() && !$testQuestion->question->hasEqualScoresForSubQuestions());
        })->count();
        return $countCarouselQuestionWithToFewQuestions != 0;
    }

    public function getHasPdfAttachmentsAttribute(): bool
    {
        return TestQuestion::select()
            ->join('question_attachments', 'test_questions.question_id', '=', 'question_attachments.question_id')
            ->join('attachments', 'question_attachments.attachment_id', '=', 'attachments.id')
            ->where('test_questions.test_id', $this->getKey())
            ->where('attachments.file_mime_type', 'application/pdf')
            ->exists();
    }

    public function getPdfAttachmentsAttribute()
    {
        $attachments = collect();

        $this->testQuestions->sortBy('order')->each(function ($testQuestion) use (&$attachments) {
            $testQuestion->question->loadRelated();
            $testQuestion->question->attachments->each(function ($attachment) use (&$attachments) {
                if ($attachment->getFileType() == 'pdf') {
                    $attachments->add($attachment);
                }
            });
        });
        return $attachments;
    }
    public function getAttachmentsAttribute()
    {
        $attachments = collect();

        $this->testQuestions->sortBy('order')->each(function ($testQuestion) use (&$attachments) {
            $testQuestion->question->loadRelated();
            $testQuestion->question->attachments->each(function ($attachment) use (&$attachments) {
                $attachments->add($attachment);
            });
        });
        return $attachments;
    }


    public function isNationalItem(): bool
    {
        return collect(Test::NATIONAL_ITEMBANK_SCOPES)->contains($this->scope);
    }

    public function meetsQuestionRequirementsForPlanning(): bool
    {
        return !!(!$this->hasDuplicateQuestions() && !$this->hasTooFewQuestionsInCarousel() && !$this->hasNotEqualScoresForSubQuestionsInCarousel());
    }

    public function canViewTestDetails(User $user): bool
    {
        return $this->hasAuthor($user) ||
            $this->isFromSchoolAndSameSection($user) ||
            ($user->schoolLocation->show_national_item_bank && $this->isNationalItemForAllowedBaseSubject()) ||
            $this->isFromAllowedTestPublisher($user) ||
            $this->isFromSharedSchoolAndAllowedBaseSubject($user) ||
            $this->canBeAccessedByExamCoordinator($user) ||
            ($user->isToetsenbakker() && $this->isStillInTheOven());
    }

    private function isFromSharedSchoolAndAllowedBaseSubject(User $user): bool
    {
        return $this->canCopyFromSchool($user) && $this->subjectIsInCurrentBaseSubjects();
    }

    public function hasAuthor(User $user): bool
    {
        return $this->testAuthors()->whereUserId($user->getKey())->exists();
    }

    private function isNationalItemForAllowedBaseSubject(): bool
    {
        return $this->isNationalItem() && $this->subjectIsInCurrentBaseSubjects();
    }

    private function subjectIsInCurrentBaseSubjects(): bool
    {
        return BaseSubject::currentForAuthUser()->whereId($this->subject()->pluck('base_subject_id'))->exists();
    }

    public static function publishedAvailableFromPublisher($publishedTestScope, User $user): bool
    {
        return self::select('s.base_subject_id')
            ->distinct()
            ->join('subjects as s', 'tests.subject_id', '=', 's.id')
            ->where('tests.scope', '=', $publishedTestScope)
            ->whereIn('s.base_subject_id', Subject::filtered(['user_current' => $user->getKey()], [])->select('base_subject_id'))
            ->exists('s.base_subject_id');
    }

    private function isFromSchoolAndSameSection($user): bool
    {
        if ($this->owner_id === $user->school_location_id) {
            return $user->sections()->where('id', $this->subject->section_id)->exists();
        }
        return false;
    }

    private function isFromAllowedTestPublisher($user): bool
    {
        if($this->scope === null || $this->scope === '') {
            return false;
        }

        return ContentSourceHelper::scopeIsAllowedForUser($user, $this->scope);
    }

    public function scopeOwner($query, SchoolLocation $schoolLocation)
    {
        return $query->where('owner_id', $schoolLocation->getKey());
    }

    public function canPlan(User $user): bool
    {
        /* You can't plan tests from shared sections, you first need to copy them */
        return !$user->schoolLocation->sharedSections->contains($this->subject->section);
    }

    private function canBeAccessedByExamCoordinator(User $user): bool
    {
        if (!$user->isValidExamCoordinator()) return false;

        return $this->owner_id === $user->school_location_id;
    }

    /**
     * @return void
     */
    private function forwardPropertyChangesToDependentModels(): void
    {
        if ($this->isDirty(['subject_id', 'education_level_id', 'education_level_year', 'draft'])) {
            $testQuestions = $this->testQuestions;
            foreach ($testQuestions as $testQuestion) {
                if ($this->propertiesAreInSyncWithQuestion($testQuestion->question)) {
                    continue;
                }
                $request = new Request();
                $params = [
                    'session_hash'         => Auth::user()->session_hash,
                    'user'                 => Auth::user()->username,
                    'id'                   => $testQuestion->id,
                    'subject_id'           => $this->subject_id,
                    'education_level_id'   => $this->education_level_id,
                    'education_level_year' => $this->education_level_year,
                    'draft'                => $this->draft,
                ];
                $testQuestionQuestionId = $testQuestion->question->id;
                $request->merge($params);
                $response = (new TestQuestionsController())->updateFromWithin($testQuestion, $request);
                if ($testQuestion->question->type == 'GroupQuestion') {
                    $testQuestion = $testQuestion->fresh();
                    $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($testQuestion->uuid);
                    foreach ($testQuestion->question->groupQuestionQuestions as $groupQuestionQuestion) {
                        $request = new Request();
                        $request->merge($params);
                        $response = (new GroupQuestionQuestionsController())->updateFromWithin($groupQuestionQuestionManager, $groupQuestionQuestion, $request);
                    }
                }
            }
        }
    }

    /**
     * @param $question
     * @return bool
     */
    private function propertiesAreInSyncWithQuestion($question): bool
    {
        return ($question->subject_id == $this->subject_id) &&
            ($question->education_level_id == $this->education_level_id) &&
            ($question->education_level_year == $this->education_level_year) &&
            ($question->draft == $this->draft);
    }

    public function scopeDraft($query)
    {
        return $query->where('draft', true);
    }

    public function scopePublished($query)
    {
        return $query->where('draft', false);
    }

    public function isStillInTheOven(): bool
    {
        return $this->owner_id === SchoolLocation::where('customer_code', config('custom.TB_customer_code'))->value('id');
    }

    /**
     * General method to get all the questions of a test, including flattened groups.
     * A callback can be provided to add properties to a question without having to
     * copy the whole thing and make the changes required.
     * @param null $addPropertyCallback
     * @return Collection
     */
    public function getFlatQuestionList($addPropertyCallback = null): Collection
    {
        return $this->testQuestions
            ->sortBy('order')
            ->flatMap(function ($testQuestion) use ($addPropertyCallback) {
                $testQuestion->question->loadRelated();
                if ($testQuestion->question->type === 'GroupQuestion') {
                    $groupQuestion = $testQuestion->question;
                    return $testQuestion->question->groupQuestionQuestions->map(
                        function ($item) use ($addPropertyCallback, $groupQuestion) {
                            $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                            if (is_callable($addPropertyCallback)) {
                                $addPropertyCallback($item, $groupQuestion);
                            }
                            return $item->question;
                        }
                    );
                }
                if (is_callable($addPropertyCallback)) {
                    $addPropertyCallback($testQuestion);
                }
                return collect([$testQuestion->question]);
            })
            ->values();
    }

    public function getBaseSubjectLanguage(string $column = 'wsc_lang'): null|string|WscLanguage
    {
        return BaseSubject::join('subjects', 'subjects.base_subject_id', '=', 'base_subjects.id')
            ->join('tests', 'tests.subject_id', '=', 'subjects.id')
            ->where('tests.id', $this->getKey())
            ->value('base_subjects.' . $column);
    }
}
