<?php namespace tcCore;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Jobs\CountTeacherTests;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Question\QuestionGatherer;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class Test extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

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

    public static function boot()
    {
        parent::boot();

        static::created(function (Test $test) {
            Queue::push(new CountTeacherTests($test->author));
        });

        static::saving(function (Test $test) {
            $dirty = $test->getDirty();
            if ((count($dirty) > 1 && array_key_exists('system_test_id', $dirty)) || (count($dirty) > 0 && !array_key_exists('system_test_id', $dirty)) && !$test->getAttribute('is_system_test')) {
                $test->setAttribute('system_test_id', null);
            }
        });

        static::deleted(function (Test $test) {
            Queue::push(new CountTeacherTests($test->author));
        });
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
        return $this->belongsTo('tcCore\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function testQuestions()
    {
        return $this->hasMany('tcCore\TestQuestion', 'test_id');
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

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
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
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
                    $query->orderBy($key, $value);
                    break;
            }
        }

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

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $user = Auth::user();
        $roles = $this->getUserRoles();
        $schoolLocation = SchoolLocation::find($user->getAttribute('school_location_id'));

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

            $query->whereIn('subject_id', function ($query) use ($user) {
                $user->subjects($query)->select('id');
            });

            $query->whereIn('author_id', function ($query) use ($user) {
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

            // TC-158  don't show demo tests from other users
            $query->where(function ($q) use ($user) {
                $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
                $q->where(function ($query) use ($user, $subject) {
                    $query->where('subject_id', $subject->getKey())->where('author_id', $user->getKey());
                })
                    ->orWhere('subject_id', '<>', $subject->getKey());
            });
        }

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

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
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
                case 'name':
                case 'abbreviation':
                case 'subject_id':
                case 'education_level_id':
                case 'education_level_year':
                case 'period_id':
                case 'test_kind_id':
                case 'status':
                case 'author_id':
                    $query->orderBy($key, $value);
                    break;
            }
        }

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
        $questions = QuestionGatherer::getQuestionsOfTest($this->getKey(), true);
        $this->setAttribute('question_count', count($questions));
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


}
