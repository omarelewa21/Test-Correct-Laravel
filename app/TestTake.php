<?php namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;
use tcCore\Events\InbrowserTestingUpdatedForTestParticipant;
use tcCore\Events\NewTestTakeGraded;
use tcCore\Events\NewTestTakePlanned;
use tcCore\Events\NewTestTakeReviewable;
use tcCore\Events\TestTakeChangeDiscussingQuestion;
use tcCore\Events\TestTakeOpenForInteraction;
use tcCore\Events\TestTakeShowResultsChanged;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\GlobalStateHelper;
use tcCore\Http\Middleware\AfterResponse;
use tcCore\Jobs\CountTeacherLastTestTaken;
use tcCore\Jobs\CountTeacherTestDiscussed;
use tcCore\Jobs\CountTeacherTestTaken;
use tcCore\Jobs\SendTestPlannedMail;
use tcCore\Jobs\SendTestRatedMail;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Models\BaseModel;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\TestParticipant\Factory;
use tcCore\Scopes\ArchivedScope;
use tcCore\Traits\Archivable;
use tcCore\Traits\UuidTrait;

class TestTake extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;
    use Archivable;

    /**
     * Stops the appended attributes from being loaded at every TestTake hydratation if false
     */
    public static $withAppends = true;

    protected $casts = [
        'uuid'                       => EfficientUuid::class,
        'notify_students'            => 'boolean',
        'show_grades'                => 'boolean',
        'returned_to_taken'          => 'boolean',
        'deleted_at'                 => 'datetime',
        'time_start'                 => 'datetime',
        'time_end'                   => 'datetime',
        'show_results'               => 'datetime',
        'exported_to_rtti'           => 'datetime',
        'assessed_at'                => 'datetime',
        'review_active'              => 'boolean',
        'results_published'          => 'datetime',
        'allow_inbrowser_colearning' => 'boolean',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_takes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_id', 'test_take_status_id', 'period_id', 'retake', 'retake_test_take_id', 'time_start', 'time_end', 'location', 'weight', 'note', 'invigilator_note', 'show_results', 'discussion_type', 'is_rtti_test_take', 'exported_to_rtti', 'allow_inbrowser_testing', 'guest_accounts', 'skipped_discussion', 'notify_students', 'user_id', 'scheduled_by', 'show_grades', 'returned_to_taken', 'discussing_question_id', 'assessed_at', 'assessment_type', 'assessing_question_id', 'allow_wsc', 'max_assessed_answer_index', 'show_correction_model', 'enable_spellcheck_colearning', 'assessing_answer_index', 'enable_comments_colearning', 'enable_answer_model_colearning', 'enable_question_text_colearning', 'review_active', 'results_published', 'enable_mr_chadd', 'allow_inbrowser_colearning'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @var array Array with invigilator IDs, for saving
     */
    protected $invigilators;

    /**
     * @var array Array with school class IDs, for saving
     */
    protected $schoolClasses;

    protected $appends = ['exported_to_rtti_formated', 'invigilators_acceptable', 'invigilators_unacceptable_message', 'directLink'];

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saving(function (TestTake $testTake) {
            $test = $testTake->test;
            if ($test === null) {
                return false;
            }
            if ($test->getAttribute('is_system_test') == 0) {
                $systemTestId = $test->getAttribute('system_test_id');
                if (empty($systemTestId)) {
                    $systemTest = $test->duplicate(['system_test_id' => $test->getKey()], null, function (Test $systemTest) use ($test) {
                        $systemTest->setAttribute('is_system_test', 1);
                    });
                    $systemTestId = $systemTest->getKey();
                    $test->setAttribute('system_test_id', $systemTestId);
                    $test->save();
                }
                $testTake->setAttribute('test_id', $systemTestId);
            }

            if ($testTake->test_take_status_id === TestTakeStatus::STATUS_DISCUSSING && $testTake->getAttribute('discussing_question_id') !== null) {
                $testTake->setAttribute('is_discussed', true);
            }

            if (Uuid::isValid($testTake->user_id)) {
                $newUserId = User::whereUuid($testTake->user_id)->value('id');
                $testTake->user_id = $newUserId ?? $testTake->getOriginal('user_id');
            }
            return true;
        });

        static::saved(function (TestTake $testTake) {
            $originalTestTakeStatus = TestTakeStatus::find($testTake->getOriginal('test_take_status_id'));

            // logging statuses if changed
            if ($testTake->isDirty('test_take_status_id')) {
                TestTakeStatusLog::create([
                    'test_take_id'        => $testTake->getKey(),
                    'test_take_status_id' => $testTake->test_take_status_id
                ]);
                // if we go from taken to discussed without actual discussing, we get a record created 8 (but in the mean time a 7 is also created as
                // initiated from the frontend, so we need to remove that record if it was in the last 60 seconds as then you did not really discuss the test take
                if ((int)$testTake->test_take_status_id === 8) {
                    TestTakeStatusLog::where('test_take_id', $testTake->getKey())->where('test_take_status_id', 7)->where('created_at', '>=', Carbon::now()->subSeconds(120))->delete();
                }

                $testTake->updateGuestAvailabilityForParticipantsOnStatusChange();
            }

            if ($testTake->invigilators !== null) {
                $testTake->saveInvigilators();
            }

            // Filling with test participants only allowed on creation, no sync (like invigilators) at the moment
            if ($testTake->schoolClasses !== null) {
                $testTake->schoolClasses = null;
            }

            // Setting test take status to 'Taking test' for active test participants
            $testTake->load('testTakeStatus');
            if ($testTake->testTakeStatus->name === 'Taking test') {
                if ($testTake->test->getAttribute('status') == 0) {
                    $test = $testTake->test;

                    $test->setAttribute('status', 1);
                    $test->save();
                }
            }

            if ($testTake->testTakeStatus->name === 'Taking test' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')) {
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Start')->value('id'));
                $testTake->testTakeEvents()->save($testTakeEvent);

                $testTake->testParticipants()->update([
                    'test_take_status_id' => TestTakeStatus::STATUS_TEST_NOT_TAKEN
                ]);

                foreach ($testTake->testParticipants as $testParticipant) {
                    AfterResponse::$performAction[] = fn() => TestTakeOpenForInteraction::dispatch($testParticipant->uuid);
                }
            }

            if ($testTake->testTakeStatus->name === 'Taken' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')) {
                $testTakeUnfinishedStatuses = TestTakeStatus::whereIn('name', ['Planned', 'Taking test', 'Handed in', 'Taken'])->pluck('id', 'name')->all();
                if (array_key_exists('Taken', $testTakeUnfinishedStatuses)) {
                    $testTakenStatusId = $testTakeUnfinishedStatuses['Taken'];
                    unset($testTakeUnfinishedStatuses[$testTakenStatusId]);
                } else {
                    $testTakenStatusId = false;
                }

                if ($testTakenStatusId !== false) {
                    foreach ($testTake->testParticipants as $testParticipant) {
                        if (in_array($testParticipant->getAttribute('test_take_status_id'), $testTakeUnfinishedStatuses)) {
                            $testParticipant->setAttribute('test_take_status_id', $testTakenStatusId);
                            $testParticipant->save();
                        }
                    }
                }

                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Stop')->value('id'));

                $testTake->testTakeEvents()->save($testTakeEvent);
            }

            if ($testTake->test_take_status_id === TestTakeStatus::STATUS_DISCUSSING) {
                if ($testTake->studentsAreInNewCoLearning()) {
                    AfterResponse::$performAction[] = fn() => TestTakeChangeDiscussingQuestion::dispatch($testTake->uuid);
                }
            }

            if ($testTake->testTakeStatus->name === 'Discussing' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')) {
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Start discussion')->value('id'));

                $testTake->testTakeEvents()->save($testTakeEvent);

                $heartbeatDate = Carbon::now();
                $heartbeatDate->subSeconds(30);

                //test_take_status_id is the ID of 'Discussing'
                $testParticipantDiscussingStatus = $testTake->getAttribute('test_take_status_id');

                $testTakeDiscussionNotAllowedStatusses = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken', 'Taken away'])->pluck('id', 'name')->all();

                $testTake->load(['testParticipants',
                    'testParticipants.schoolClass' => function ($query) {
                        return $query->withTrashed();
                    },
                    'testParticipants.schoolClass.schoolLocation'
                ]);
                foreach ($testTake->testParticipants as $testParticipant) {
                    if (!in_array($testParticipant->getAttribute('test_take_status_id'), $testTakeDiscussionNotAllowedStatusses)) {
                        $testParticipant->setAttribute('test_take_status_id', $testParticipantDiscussingStatus);
                        $testParticipant->save();
                    }
                    AnswerChecker::checkAnswerOfParticipant($testParticipant);
                }
            }
            if (($testTake->testTakeStatus->name === 'Discussing' && ($testTake->getAttribute('discussing_question_id') != $testTake->getOriginal('discussing_question_id') || $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')))
                || ($testTake->testTakeStatus->name === 'Discussed' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id'))) {
                $inactiveTestParticipant = [];
                $testTakeDiscussedStatus = TestTakeStatus::where('name', 'Discussing')->value('id');
                foreach ($testTake->testParticipants as $testParticipant) {
                    if ($testTakeDiscussedStatus != $testParticipant->getAttribute('test_take_status_id')) {
                        $inactiveTestParticipant[] = $testParticipant->getAttribute('user_id');
                    }

                    AfterResponse::$performAction[] = fn() => TestTakeOpenForInteraction::dispatch($testParticipant->uuid);
                }
                AnswerRating::where('test_take_id', $testTake->getKey())->whereIn('answer_id', function ($query) use ($testTake) {
                    $answer = new Answer();
                    $query->select('id')->from($answer->getTable())->where('question_id', $testTake->getOriginal('discussing_question_id'));
                })->whereIn('user_id', $inactiveTestParticipant)->where('type', 'STUDENT')->whereNull('rating')->delete();
            }


            if ($testTake->testTakeStatus->name === 'Discussed' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')) {
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'End discussion')->value('id'));
                $testTake->testTakeEvents()->save($testTakeEvent);

                $testParticipantDiscussedStatus = $testTake->getAttribute('test_take_status_id');
                $testTakeDiscussedStatus = TestTakeStatus::where('name', 'Discussing')->value('id');

                foreach ($testTake->testParticipants as $testParticipant) {
                    if ($testTakeDiscussedStatus == $testParticipant->getAttribute('test_take_status_id')) {
                        $testParticipant->setAttribute('test_take_status_id', $testParticipantDiscussedStatus);
                        $testParticipant->save();
                    }
                }
            }

            if ((($testTake->testTakeStatus->name === 'Taken' || $testTake->testTakeStatus->name === 'Discussing' || $testTake->testTakeStatus->name === 'Discussed' || $testTake->testTakeStatus->name === 'Rated')
                    && ($originalTestTakeStatus === null || $originalTestTakeStatus->name === 'Planned' || $originalTestTakeStatus->name === 'Taking test' || $originalTestTakeStatus->name === 'Taken'))
                || (($testTake->testTakeStatus->name === 'Planned' || $testTake->testTakeStatus->name === 'Taking test' || $testTake->testTakeStatus->name === 'Taken')
                    && ($originalTestTakeStatus === null || $originalTestTakeStatus->name === 'Taken' || $originalTestTakeStatus->name === 'Discussing' || $originalTestTakeStatus->name === 'Discussed' || $originalTestTakeStatus->name === 'Rated'))) {
                $schoolClassIds = $testTake->testParticipants()->distinct()->pluck('school_class_id');
                $subjectId = $testTake->test->getAttribute('subject_id');

                $users = User::whereIn('id', function ($query) use ($schoolClassIds, $subjectId) {
                    $teacher = new Teacher();
                    $query->select('user_id')->from($teacher->getTable())->where('subject_id', $subjectId)->whereIn('class_id', $schoolClassIds);
                })->get();

                foreach ($users as $user) {
                    Queue::push(new CountTeacherTestTaken($user));
                    Queue::push(new CountTeacherLastTestTaken($user));
                }
            }

            if ($testTake->testTakeStatus->name === 'Rated' && $originalTestTakeStatus !== null && $originalTestTakeStatus->name !== 'Rated') {
                if (GlobalStateHelper::getInstance()->isQueueAllowed()) {
                    Queue::later(300, new SendTestRatedMail($testTake));
                }

                $testTake->testParticipants->each(function ($participant) {
                    AfterResponse::$performAction[] = fn() => NewTestTakeGraded::dispatch($participant->user()->value('uuid'));
                });
            }

            if ($testTake->getAttribute('is_discussed') != $testTake->getOriginal('is_discussed')) {
                $schoolClassIds = $testTake->testParticipants()->distinct()->pluck('school_class_id');
                $subjectId = $testTake->test->getAttribute('subject_id');

                $users = User::whereIn('id', function ($query) use ($schoolClassIds, $subjectId) {
                    $teacher = new Teacher();
                    $query->select('user_id')->from($teacher->getTable())->where('subject_id', $subjectId)->whereIn('class_id', $schoolClassIds);
                })->get();

                foreach ($users as $user) {
                    Queue::push(new CountTeacherTestDiscussed($user));
                }
            }

            $testTake->handleInbrowserTestingChangesForParticipants();
            $testTake->createTestTakeCodeIfNeeded();
            $testTake->handleShowResultChanges();
            $testTake->updateGuestRatingVisibilityWindow();
        });

        static::creating(function (TestTake $testTake) {
            if ($testTake->school_location_id === null) {
                $testTake->school_location_id = Auth::user()->school_location_id;
            }
            $testTake->scheduled_by = auth()->id();
        });

        static::created(function (TestTake $testTake) {
            if ($testTake->schoolClasses !== null) {
                $testTake->saveSchoolClassTestTakeParticipants();
                $testTake->dispatchNewTestTakePlannedEvent();
            }
            if ($testTake->notify_students && GlobalStateHelper::getInstance()->isQueueAllowed()) {
                Queue::later(300, new SendTestPlannedMail($testTake->getKey()));
            }
        });

        static::deleted(function (TestTake $testTake) {
            $schoolClassIds = $testTake->testParticipants()->distinct()->pluck('school_class_id');
            $subjectId = $testTake->test->getAttribute('subject_id');

            $users = User::whereIn('id', function ($query) use ($schoolClassIds, $subjectId) {
                $teacher = new Teacher();
                $query->select('user_id')->from($teacher->getTable())->where('subject_id', $subjectId)->whereIn('class_id', $schoolClassIds);
            })->get();

            foreach ($users as $user) {
                Queue::push(new CountTeacherTestTaken($user));
                Queue::push(new CountTeacherTestDiscussed($user));
                Queue::push(new CountTeacherLastTestTaken($user));
            }
        });
    }

    public function test()
    {
        return $this->belongsTo('tcCore\Test');
    }

    public function testTakeCode()
    {
        return $this->hasOne(TestTakeCode::class);
    }

    public function user()
    {
        return $this->belongsTo('tcCore\User')->withTrashed();
    }

    public function scheduledByUser()
    {
        return $this->belongsTo(User::class, 'scheduled_by')->withTrashed();
    }

    public function retakeTestTake()
    {
        return $this->belongsTo('tcCore\TestTake', 'retake_test_take_id');
    }

    public function testTakeStatus()
    {
        return $this->belongsTo('tcCore\TestTakeStatus');
    }

    public function testTakeEvents()
    {
        return $this->hasMany('tcCore\TestTakeEvent');
    }

    public function period()
    {
        return $this->belongsTo('tcCore\Period');
    }

    public function discussingQuestion()
    {
        return $this->belongsTo('tcCore\Question', 'discussing_question_id');
    }

    public function discussingParentQuestions()
    {
        return $this->hasMany('tcCore\DiscussingParentQuestion', 'test_take_id');
    }

    public function testParticipants()
    {
        return $this->hasMany('tcCore\TestParticipant');
    }

    public function testRatings()
    {
        return $this->hasMany('tcCore\TestRating');
    }

    public function schoolClasses()
    {
        return SchoolClass::fromTestTakes($this->getKey());
    }

    public static function schoolClassesForMultiple($testTakeIds)
    {
        return SchoolClass::fromTestTakes($testTakeIds);
    }

    public function schoolLocation()
    {
        return $this->belongsTo(SchoolLocation::class);
    }

    public function invigilators()
    {
        return $this->hasMany('tcCore\Invigilator');
    }

    public function invigilatorUsers()
    {
        return $this->belongsToMany('tcCore\User', 'invigilators')
            ->withTrashed()
            ->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])
            ->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function answerRatings()
    {
        return $this->hasMany(AnswerRating::class, 'test_take_id');
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

    public function isAllowedToView(User $userToCheck, bool $asInvigilator = true, bool $asExamCoordinator = true)
    {
        return ($this->hasParticipantsThatUserTeaches($userToCheck) && $userToCheck->hasAccessToTest($this->test))
            || ($asInvigilator && $this->isInvigilator($userToCheck))
            || (
                    ($this->isScheduledByUser($userToCheck) && !$userToCheck->isValidExamCoordinator())
                    || ($asExamCoordinator && $this->isScheduledByUser($userToCheck) && $userToCheck->isValidExamCoordinator())
                    ||
                        ($asExamCoordinator
                            && $userToCheck->isValidExamCoordinator()
                            && $this->school_location_id === $userToCheck->school_location_id
                            && (in_array($this->test_take_status_id, [(string)TestTakeStatus::STATUS_PLANNED, (string)TestTakeStatus::STATUS_RATED]))
                        )
            )
            || $this->isTakeOwner($userToCheck);
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (array_key_exists('invigilators', $attributes)) {
            $this->invigilators = $attributes['invigilators'];
        }

        if (array_key_exists('school_classes', $attributes)) {
            $this->schoolClasses = $attributes['school_classes'];
        }

        return $this;
    }

    public function saveInvigilators($newInvigilators = null)
    {
        $newInvigilators ??= $this->invigilators;
        $invigilators = $this->invigilators()->withTrashed()->get();

        $this->syncTcRelation($invigilators, $newInvigilators, 'user_id', function ($takeTake, $invigilator) {
            Invigilator::create(['user_id' => $invigilator, 'test_take_id' => $takeTake->getKey()]);
        });

        $this->invigilators = null;
    }

    public function saveSchoolClassTestTakeParticipants()
    {
        $testTakeParticipantFactory = new Factory(new TestParticipant());
        $testParticipants = $testTakeParticipantFactory->generateMany($this, ['school_class_ids' => $this->schoolClasses, 'test_take_status_id' => with(TestTakeStatus::where('name', 'Planned')->first())->getKey()]);
        $this->testParticipants()->saveMany($testParticipants);
        $this->schoolClasses = null;
    }

    public function dispatchNewTestTakePlannedEvent(): void
    {
        $this->testParticipants()
            ->join('users', 'users.id', '=', 'test_participants.user_id')
            ->select('users.uuid')
            ->distinct()
            ->get()
            ->pluck('uuid')
            ->each(function ($userUuid) {
                AfterResponse::$performAction[] = fn() => NewTestTakePlanned::dispatch($userUuid);
            });
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = $this->getUserRoles();
        /** todo: uitzoeken waar het scenario en Teacher en Student overgaat */
        if (in_array('Teacher', $roles) && in_array('Student', $roles)) {
            /* 2023-02-08 if this message is not seen in bugsnag, it is probably okay to delete this scenario. */
            \Bugsnag::notify('TestTake::filtered scenario Teacher and Student');
            $query->where(function ($query) {
                $query->whereIn('test_id', function ($query) {
                    $query->select('id')
                        ->from(with(new Test())->getTable())
                        ->where('user_id', Auth::id())
                        ->where('deleted_at', null);
                })
                    ->orWhere('user_id', Auth::id())
                    ->orWhereIn($this->getTable() . '.id', function ($query) {
                        $query->select('test_take_id')
                            ->from(with(new TestParticipant())->getTable())
                            ->where('user_id', Auth::id())
                            ->where('deleted_at', null);
                    });
            });
        } elseif (in_array('Teacher', $roles)) {
            $user = Auth::user();
            $skipDefaults = $user->isValidExamCoordinator() && (
                    $this->hasRatedTestTakesFilter($filters)
                    || $this->hasPlannedTestTakesFilter($filters) // as per TCP-3479
                );
            $query->when(!$skipDefaults, function ($query) use ($filters, $user) {
                $query->accessForTeacher($user, $filters)
                    ->withoutDemoTeacherForUser($user)
                    ->onlyTestsFromSubjectsOrIfDemoThenOnlyWhenOwner($user)
                    ->when($user->isValidExamCoordinator(), fn($query) => $query->scheduledByExamCoordinator($user));
            });
        } elseif (in_array('Student', $roles)) {
            $query->whereIn($this->getTable() . '.id', function ($query) {
                $query->select('test_take_id')
                    ->from(with(new TestParticipant())->getTable())
                    ->where('user_id', Auth::id())
                    ->where('deleted_at', null);
            });
        }

        $query->belongsToSchoolLocation(Auth::user());

        $testTable = with(new Test())->getTable();
        $query->select($this->getTable() . '.*')
            ->join($testTable, $testTable . '.id', '=', $this->getTable() . '.test_id');

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'type_not_assignment':
                    $query->TypeNotAssignment();
                    break;
                case 'takeUuid':
                    $query->where('test_takes.id', self::whereUuid($value)->value('id'));
                    break;
                case 'type_assignment':
                    $query->typeAssignment();
                    break;
                case 'user_id':
                    if (is_array($value)) {
                        $query->whereIn('user_id', $value);
                    } else {
                        $query->where('user_id', '=', $value);
                    }
                    break;
                case 'test_id':
                    if (is_array($value)) {
                        $query->whereIn('test_id', $value);
                    } else {
                        $query->where('test_id', '=', $value);
                    }
                    break;
                case 'period_id':
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    $query->whereIn($this->getTable() . '.period_id', $value);
                    break;
                case 'retake':
                    $query->where('retake', '=', $value);
                    break;
                case 'retake_test_id':
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    $query->whereIn('retake_test_take_id', $value);
                    break;
                case 'test_take_status_id':
                    $query->whereIn('test_take_status_id', Arr::wrap($value));
                    break;
                case 'time_start_from':
                    $query->where('time_start', '>=', $value);
                    break;
                case 'time_start_to':
                    $query->where('time_start', '<=', $value);
                    break;
                case 'time_end_from':
                    $query->where('time_end', '>=', $value);
                    break;
                case 'time_end_to':
                    $query->where('time_end', '<=', $value);
                    break;
                case 'show_results_from':
                    $query->where('show_results', '>=', $value);
                    break;
                case 'show_results_to':
                    $query->where('show_results', '<=', $value);
                    break;
                case 'started_from':
                case 'started_to':
                    $values = [];
                    if (array_key_exists('started_from', $filters)) {
                        $values['started_from'] = $filters['started_from'];
                        if ($key !== 'started_from') {
                            unset($filters['started_from']);
                        }
                    }

                    if (array_key_exists('started_to', $filters)) {
                        $values['started_to'] = $filters['started_to'];
                        if ($key !== 'started_to') {
                            unset($filters['started_to']);
                        }
                    }

                    $query->whereIn($this->getKeyName(), function ($query) use ($values) {
                        $query->select('test_take_id')
                            ->from(with(new TestTakeEvent())->getTable())
                            ->where('deleted_at', null)
                            ->where('test_take_event_type_id', '=', TestTakeEventType::where('name', 'Start')->whereNull('test_participant_id')->value('id'));
                        if (array_key_exists('started_from', $values)) {
                            $query->where('created_at', '>=', $values['started_from']);
                        }
                        if (array_key_exists('started_to', $values)) {
                            $query->where('created_at', '<=', $values['started_to']);
                        }
                    });
                    break;
                case 'stopped_from':
                case 'stopped_to':
                    $values = [];
                    if (array_key_exists('stopped_from', $filters)) {
                        $values['stopped_from'] = $filters['stopped_from'];
                        if ($key !== 'stopped_from') {
                            unset($filters['stopped_from']);
                        }
                    }

                    if (array_key_exists('stopped_to', $filters)) {
                        $values['stopped_to'] = $filters['stopped_to'];
                        if ($key !== 'stopped_to') {
                            unset($filters['stopped_to']);
                        }
                    }

                    $query->whereIn($this->getKeyName(), function ($query) use ($values) {
                        $query->select('test_take_id')
                            ->from(with(new TestTakeEvent())->getTable())
                            ->where('deleted_at', null)
                            ->where('test_take_event_type_id', '=', TestTakeEventType::where('name', 'Stop')->value('id'));
                        if (array_key_exists('stopped_from', $values)) {
                            $query->where('created_at', '>=', $values['stopped_from']);
                        }
                        if (array_key_exists('stopped_to', $values)) {
                            $query->where('created_at', '<=', $values['stopped_to']);
                        }
                    });
                    break;
                case 'school_class_id':
                    if (is_array($value)) {
                        $query->whereIn($this->getTable() . '.id', TestParticipant::whereIn('school_class_id', $value)->distinct()->select('test_take_id'));
                    } else {
                        $query->whereIn($this->getTable() . '.id', TestParticipant::where('school_class_id', $value)->distinct()->select('test_take_id'));
                    }
                    break;
                case 'school_class_name':
                    $query->whereIn(
                        $this->getTable() . '.id',
                        TestParticipant::whereHas('schoolClass', function ($q) use ($value) {
                            $q->where('name', 'LIKE', '%' . $value . '%');
                        })->distinct()
                            ->select('test_take_id'));
                    break;
                case 'location':
                    $query->where('location', 'LIKE', '%' . $value . '%');
                    break;
                case 'weight':
                    $query->where('weight', '=', $value);
                    break;
                case 'subject_id':
                    $query->whereIn(
                        $this->getTable() . '.id',
                        TestTake::distinctTestTakesFromTests()
                            ->when(is_array($value),
                                fn($query) => $query->whereIn('tests.subject_id', $value),
                                fn($query) => $query->where('tests.subject_id', $value)
                            )
                    );
                    break;
                case 'test_name':
                    $query->whereIn(
                        $this->getTable() . '.id',
                        TestTake::distinctTestTakesFromTests()->where('tests.name', 'LIKE', "%$value%")
                    );
                    break;
            }
        }

        //Todo: More sorting
        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'user_id':
                case 'test_id':
                case 'test_take_status_id':
                case 'period_id':
                case 'retake':
                case 'retake_test_take_id':
                case 'time_start':
                case 'time_end':
                case 'location':
                case 'weight':
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
                case 'user_id':
                case 'test_id':
                case 'test_take_status_id':
                case 'period_id':
                case 'retake':
                case 'retake_test_take_id':
                case 'time_start':
                case 'time_end':
                case 'location':
                case 'weight':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    /**
     * @param User $userToCheck
     * @return bool
     */
    public function isInvigilator(User $userToCheck): bool
    {
        $allowed = false;
        foreach ($this->invigilatorUsers as $user) {
            if ($user->getKey() == $userToCheck->getKey()) {
                $allowed = true;
                $pdo = DB::connection()->getPdo()->exec('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
                break;
            }
        }
        return $allowed;
    }

    public function scopeNotDemo($query, $tableAlias = null)
    {
        if (!$tableAlias) {
            $tableAlias = $this->getTable();
        }

        return $query->where(sprintf('%s.demo', $tableAlias), 0);
    }

    public function hasCarousel()
    {
        return GroupQuestion::whereIn(
            'id',
            $this->test->testQuestions()->select('question_id')
        )
            ->where('groupquestion_type', 'carousel')
            ->exists();
    }

    public function giveAbbreviatedInvigilatorNames()
    {
        $invigilators = $this->invigilatorUsers()->withTrashed()->get()->map(function ($invigilator) {
            return $invigilator->getFullNameWithAbbreviatedFirstName();
        });

        return collect($invigilators);
    }

    public function getExportedToRttiFormatedAttribute()
    {
        if ($this->shouldNotAppend()) {
            return null;
        }

        return array_key_exists('exported_to_rtti', $this->attributes) && $this->attributes['exported_to_rtti'] ? Carbon::parse($this->attributes['exported_to_rtti'])->format('d-m-Y H:i:s') : 'Nog niet geëxporteerd';
    }

    public function getInvigilatorsAcceptableAttribute()
    {
        if ($this->shouldNotAppend()) {
            return null;
        }

        if ($this->hasValidInvigilators()) {
            return true;
        }
        return false;
        if ($this->hasRemovedInvigilators()) {
            $invigilatorsRemoved = true;
        }
    }

    public function getInvigilatorsUnacceptableMessageAttribute()
    {
        if ($this->shouldNotAppend()) {
            return null;
        }

        if ($this->hasValidInvigilators()) {
            return '';
        }
        if ($this->hasRemovedInvigilators()) {
            return __('De surveilant is niet langer actief binnen Test-Correct');
        }
        return _('Er is geen surveillant gekoppeld');
    }

    public function getDirectLinkAttribute()
    {
        if ($this->shouldNotAppend()) {
            return null;
        }

        return config('app.base_url') . "directlink/" . $this->uuid;
    }

    private function createTestTakeCodeIfNeeded()
    {
        if ($this->testTakeCode()->doesntExist()) {
            $this->testTakeCode()->create();
        }
    }

    public function determineTestTakeStage()
    {
        $status = $this->test_take_status_id;

        $planned = [TestTakeStatus::STATUS_PLANNED, TestTakeStatus::STATUS_TEST_NOT_TAKEN, TestTakeStatus::STATUS_TAKING_TEST];
        $discuss = [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING];
        $review = [TestTakeStatus::STATUS_DISCUSSED];
        $graded = [TestTakeStatus::STATUS_RATED];

        if (in_array($status, $planned)) return 'planned';
        if (in_array($status, $discuss)) return 'discuss';
        if (in_array($status, $review)) return 'review';
        if (in_array($status, $graded)) return 'graded';

        return null;
    }

    public static function getTestTakeWithSubjectNameAndTestName($testTakeId)
    {
        if (Uuid::isValid($testTakeId)) {
            $testTakeId = TestTake::whereUuid($testTakeId)->value('id');
        }
        return TestTake::select('test_takes.*', 'subjects.name as subject_name', 'tests.name as test_name')
            ->join('tests', 'test_takes.test_id', '=', 'tests.id')
            ->join('subjects', 'tests.subject_id', '=', 'subjects.id')
            ->where('test_takes.id', $testTakeId)
            ->first();
    }

    private function updateGuestAvailabilityForParticipantsOnStatusChange()
    {
        $this->testParticipants()
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'test_participants.user_id')
                    ->where('users.guest', true);
            })
            ->update(['test_participants.available_for_guests' => true]);
    }

    private function handleInbrowserTestingChangesForParticipants()
    {
        if ($this->isDirty('allow_inbrowser_testing')) {
            $this->testParticipants()->update(['allow_inbrowser_testing' => $this->allow_inbrowser_testing]);
            $this->testParticipants->each(function ($participant) {
                AfterResponse::$performAction[] = fn() => InbrowserTestingUpdatedForTestParticipant::dispatch($participant->uuid);
            });
        }
    }

    private function handleShowResultChanges()
    {
        if ($this->wasChanged('show_results')) {
            AfterResponse::$performAction[] = fn() => TestTakeShowResultsChanged::dispatch($this->uuid);

            $this->testParticipants->each(function ($participant) {
                AfterResponse::$performAction[] = fn() => NewTestTakeReviewable::dispatch($participant->user()->value('uuid'));
            });
        }
    }

    private function orUserHasAccessToSchoolClassParticipantsAndSubjectScope($query, User $user)
    {
        $query->orWhereIn($this->getTable() . '.id', function ($query) use ($user) {
            $currentSchoolYearId = SchoolYearRepository::getCurrentSchoolYear()->getKey();
            $teacherTable = with((new Teacher)->getTable());
            $schoolClassTable = with((new SchoolClass())->getTable());
            $query->select('test_take_id')
                ->from(with(new TestParticipant())->getTable())
                ->whereNull('deleted_at')
                ->whereIn('school_class_id',
                    function ($query) use ($teacherTable, $schoolClassTable, $currentSchoolYearId, $user) {
                        $query->select('class_id')
                            ->from($teacherTable)
                            ->join($schoolClassTable, "$teacherTable.class_id", '=', "$schoolClassTable.id")
                            ->where('user_id', $user->id)
                            ->where('school_year_id', $currentSchoolYearId)
                            ->whereNull("$teacherTable.deleted_at");
                        //  ->whereNull("$schoolClassTable.deleted_at");
                    })
                ->whereIn($this->getTable() . '.id',
                    function ($query) use ($teacherTable, $schoolClassTable, $currentSchoolYearId) {
                        $testTable = with(new Test())->getTable();
                        $query
                            ->select($this->getTable() . '.id')
                            ->from($this->getTable())
                            ->join($testTable, $testTable . '.id', '=', $this->getTable() . '.test_id')
                            ->whereNull($testTable . '.deleted_at')
                            ->whereIn($testTable . '.subject_id',
                                function ($query) use ($teacherTable, $schoolClassTable, $currentSchoolYearId) {
                                    $query->select('subject_id')
                                        ->from($teacherTable)
                                        ->join($schoolClassTable, "$teacherTable.class_id", '=', "$schoolClassTable.id")
                                        ->where('user_id', Auth::id())
                                        ->where('school_year_id', $currentSchoolYearId)
                                        ->whereNull("$teacherTable.deleted_at");
                                    // ->whereNull("$schoolClassTable.deleted_at");
                                });
                    });
        });
        return $this;
    }

    private function orUserIsInvigilatorScope($query, User $user, $filters = [])
    {
        if (!$this->canUseInvigilatorScope($filters)) {
            return $this;
        }

        $query->orWhereIn($this->getTable() . '.id', function ($query) use ($user) {
            $query->select('test_take_id')
                ->from(with(new Invigilator())->getTable())
                ->where('user_id', $user->id)
                ->where('deleted_at', null);
        });
        return $this;
    }

    private function orUserIsCreatorScope($query, User $user)
    {
        $query->orWhere('test_takes.user_id', $user->id);
        return $this;
    }

    public function scopeAccessForTeacher($query, User $user, $filters = [])
    {
        $query->where(function ($query) use ($filters, $user) {
            $this
                ->orUserIsCreatorScope($query, $user)
                ->orUserIsInvigilatorScope($query, $user, $filters)
                ->orUserHasAccessToSchoolClassParticipantsAndSubjectScope($query, $user);
        });
    }

    public function scopeBelongsToSchoolLocation($query, User $user)
    {
        $query->where($this->getTable() . '.school_location_id', $user->school_location_id);
    }

    public function scopeWithoutDemoTeacherForUser($query, User $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->where($this->getTable() . '.demo', 1)
                    ->where($this->getTable() . '.user_id', $user->getKey());
            })->orWhere($this->getTable() . '.demo', 0);
        });
    }

    public function scopeOnlyTestsFromSubjectsOrIfDemoThenOnlyWhenOwner($query, User $user)
    {

        $query->where(function ($q) use ($user) {
            $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
            if ($subject === null) {
                return; // no demo environments any more for new teachers except for the temporary school location
            }

            $q->whereIn($this->getTable() . '.id', function ($query) use ($subject, $user) {
                $testTable = with(new Test())->getTable();
                $query
                    ->select($this->getTable() . '.id')
                    ->from($this->getTable())
                    ->join($testTable, $testTable . '.id', '=', $this->getTable() . '.test_id')
                    ->whereNull($testTable . '.deleted_at')
                    ->where(function ($query) use ($subject, $user, $testTable) {
                        $query->where(function ($query) use ($testTable, $subject, $user) {
                            $query->where($testTable . '.subject_id', $subject->getKey())
                                ->where($testTable . '.author_id', $user->getKey());
                        })
                            ->orWhere($testTable . '.subject_id', '<>', $subject->getKey());
                    });

            });
        });
    }

    private function updateGuestRatingVisibilityWindow()
    {
        if (!$this->test_take_status_id == TestTakeStatus::STATUS_RATED || !$this->guest_accounts || $this->testTakeCode == null) {
            return;
        }

        $this->testTakeCode->setAttribute('rating_visible_expiration', Carbon::now()->addMonths(2))->save();
    }

    public function reviewingIsPossible()
    {
        return $this->show_results && $this->show_results->gt(Carbon::now());
    }

    private function hasValidInvigilators()
    {
        foreach ($this->invigilatorUsers as $invigilator) {
            if (is_null($invigilator->deleted_at)) {
                return true;
            }
        }
        return false;
    }

    private function hasRemovedInvigilators()
    {
        foreach ($this->invigilatorUsers as $invigilator) {
            if (!is_null($invigilator->deleted_at)) {
                return true;
            }
        }
        return false;
    }

    public function scopeTypeAssignment(Builder $query)
    {
        return $query->when(
            !self::isJoined($query, 'tests'),
            function ($query) {
                $query->join('tests', 'test_takes.test_id', 'tests.id');
            }
        )->where('tests.test_kind_id', TestKind::ASSIGNMENT_TYPE);
    }

    public function scopeTypeNotAssignment(Builder $query)
    {
        return $query->when(
            !self::isJoined($query, 'tests'),
            function ($query) {
                $query->join('tests', 'test_takes.test_id', 'tests.id');
            }
        )->where('test_kind_id', '<>', TestKind::ASSIGNMENT_TYPE);
    }

    public function scopeStatusPlanned(Builder $query)
    {
        return $query->where('test_take_status_id', TestTakeStatus::STATUS_PLANNED);
    }

    public function scopeStatusTakingTest(Builder $query)
    {
        return $query->where('test_take_status_id', TestTakeStatus::STATUS_TAKING_TEST);
    }

    public function scopeShouldStart(Builder $query)
    {
        return $query->where('time_start', '<', now())
            ->where('time_end', '>', now());

    }

    public function scopeShouldEnd(Builder $query)
    {
        return $query->where('time_end', '<', now());
    }

    public function updateToTakingTest()
    {
        $this->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
        $this->save();
    }

    public function updateToTaken()
    {
        $this->test_take_status_id = TestTakeStatus::STATUS_TAKEN;
        $this->discussing_question_id = null;
        $this->is_discussed = 0;
        $this->discussion_type = null;
        $this->show_results = null;
        $this->skipped_discussion = 0;
        $this->returned_to_taken = 1;
        $this->save();
    }

    public function updateToDiscussed()
    {
        $this->test_take_status_id = TestTakeStatus::STATUS_DISCUSSED;
        $this->save();
    }

    public function updateToRated(): void
    {
        $this->test_take_status_id = TestTakeStatus::STATUS_RATED;
        $this->save();
    }

    public static function isJoined($query, $table)
    {
        $joins = $query->getQuery()->joins;
        if ($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }

    public function scopeGradedTakesWithParticipantForUser($query, $user = null, $withNullRating = true)
    {
        $user = $user ?? Auth::user();
        $query->where(function ($query) {
            $query->where('test_take_status_id', TestTakeStatus::STATUS_RATED);
        })
            ->whereIn('test_takes.id', function ($query) use ($withNullRating, $user) {
                $query->select('test_take_id')
                    ->from(with(new TestParticipant())->getTable())
                    ->where('user_id', $user->getKey())
                    ->where('deleted_at', null)
                    ->when(!$withNullRating, function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('test_participants.rating')
                                ->orWhere('test_participants.retake_rating', '!=', null);
                        });
                    });
            })
            ->with([
                'testParticipants' => function ($query) use ($user) {
                    $query->select('id', 'test_take_id', 'user_id', 'rating', 'retake_rating', 'updated_at')
                        ->where('user_id', $user->getKey());
                }]);
    }

    public function maxScore($ignoreQuestions = [])
    {
        foreach ($ignoreQuestions as $key => $value) {
            if (!strstr($value, '.')) {
                continue;
            }
            $arr = explode('.', $value);
            $ignoreQuestions[$key] = $arr[1];
        }
        return $this->test->maxScore($ignoreQuestions);
    }

    public function scopeWithCardAttributes($query, $attributes = null)
    {
        return $query
            ->with(['test' => fn($query) => $query->withCount('testQuestions')])
            ->with([
                'user:id,name,name_first,name_suffix',
                'test.subject:id,name',
                'testParticipants:id,user_id,test_take_id,test_take_status_id,school_class_id',
                'testParticipants.schoolClass:id,name',
            ]);
    }

    public static function distinctTestTakesFromTests()
    {
        return TestTake::withoutGlobalScope(ArchivedScope::class)
            ->select(['test_takes.id'])
            ->join('tests', 'tests.id', '=', 'test_takes.test_id')
            ->whereNull('tests.deleted_at')
            ->distinct();
    }

    public static function redirectToDetail($testTakeUuid, $returnRoute = '', ?string $pageAction = null)
    {
        if ($pageAction) {
            $detailUrl = sprintf('test_takes/view/%s', $testTakeUuid);
            $temporaryLogin = TemporaryLogin::createWithOptionsForUser(
                ['page', 'return_route', 'page_action'],
                [$detailUrl, $returnRoute, $pageAction],
                auth()->user()
            );
            return redirect($temporaryLogin->createCakeUrl());
        }

        return CakeRedirectHelper::redirectToCake(
            routeName: 'test_takes.view',
            uuid: $testTakeUuid,
            returnRoute: $returnRoute,
        );
    }

    public function getParticipantTakenStats()
    {
        $this->loadMissing('testParticipants');
        return [
            'taken' => $this->testParticipants->filter(function ($participant) {
                return TestTakeStatus::testTakenStatusses()->contains($participant->test_take_status_id);
            })->count(),

            'notTaken' => $this->testParticipants->filter(function ($participant) {
                return !TestTakeStatus::testTakenStatusses()->contains($participant->test_take_status_id);
            })->count(),
        ];
    }

    public function scopeScheduledByExamCoordinator($query, User $user)
    {
        return $query->orWhere('test_takes.scheduled_by', $user->getKey());
    }

    public function isScheduledByUser(User $user)
    {
        return $this->scheduled_by === $user->getKey();
    }

    public function getScheduledByUserNameAttribute()
    {
        return optional(User::select(['id', 'name', 'name_suffix', 'name_first'])->whereId($this->scheduled_by)->first())->name_full;
    }

    public function isAssignmentType()
    {
        return $this->test->test_kind_id == TestKind::ASSIGNMENT_TYPE;
    }

    public function isTakeOwner(User $user): bool
    {
        return $this->user_id === $user->getKey();
    }

    private function hasRatedTestTakesFilter($filters): bool
    {
        if (!isset($filters['test_take_status_id'])) return false;

        return $filters['test_take_status_id'] == (string)TestTakeStatus::STATUS_RATED;
    }

    private function hasPlannedTestTakesFilter($filters): bool
    {
        if (!isset($filters['test_take_status_id'])) return false;

        return $filters['test_take_status_id'] == (string)TestTakeStatus::STATUS_PLANNED;
    }

    private function hasParticipantsThatUserTeaches(User $user): bool
    {
        return TestParticipant::select('test_take_id')
            ->whereIn('school_class_id',
                Teacher::select('class_id')->whereUserId($user->getKey())
            )
            ->whereTestTakeId($this->getKey())
            ->exists();
    }


    /**
     * Check if test take is still allowed to review by students
     */
    public function isAllowedToReviewResultsByParticipants(): bool
    {
        return
            !empty($this->show_results)
            && !is_null($this->show_results)
            && time() < strtotime($this->show_results);
    }

    /**
     * Check if all participant answers are rated
     */
    public function hasAllParticipantAnswersRated(): bool
    {
        return $this->testParticipants()
            ->join('answers', 'answers.test_participant_id', 'test_participants.id')
            ->whereNull('answers.final_rating')
            ->doesntExist();
    }

    private function canUseInvigilatorScope($filters): bool
    {
        if ($filters instanceof Collection) {
            $filters = $filters->toArray();
        }
        if (!array_key_exists('test_take_status_id', $filters)) {
            return true;
        }

        return empty(
        array_diff(
            Arr::wrap($filters['test_take_status_id']),
            [TestTakeStatus::STATUS_PLANNED, TestTakeStatus::STATUS_TAKING_TEST]
        )
        );
    }


    /**
     * Stop appended attributes from being loaded at every TestTake hydratation
     **/
    public function shouldNotAppend(): bool
    {
        return !static::$withAppends;
    }

    public function studentsAreInNewCoLearning()
    {
        return ($this->schoolLocation->allow_new_co_learning || $this->schoolLocation->allow_new_co_learning_teacher);
    }

    public function isDiscussionTypeOpenOnly()
    {
        return $this->getAttribute('discussion_type') === 'OPEN_ONLY';
    }

    /**
     * @param TestTake $testTake
     * @return array
     */
    public function getDottedDiscussingQuestionIdWithOptionalGroupQuestionId(?TestParticipant $testParticipant = null): ?string
    {
        $discussingQuestion = $testParticipant?->discussingQuestion ?? $this->discussingQuestion;

        $discussingQuestionGroupQuestionId = $discussingQuestion?->getGroupQuestionIdByTest($this->test->getKey());

        return collect([$discussingQuestionGroupQuestionId, $discussingQuestion?->getKey()])
            ->filter() // remove null values and if so prevent join() from adding a dot
            ->join('.');
    }

    public function getPlannedTestOptions()
    {
        return TemporaryLogin::buildValidOptionObject(
            'page',
            $this->isAssignmentType()
                ? sprintf("test_takes/assignment_open_teacher/%s", $this->uuid)
                : sprintf("test_takes/view/%s", $this->uuid)
        );
    }

    public function startTake(): void
    {
        $this->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
        $this->save();
    }

    public function getTestNameAttribute()
    {
        $testName = Arr::has($this->attributes, 'test_name')
            ? $this->attributes['test_name']
            : $this->test->name;
        return html_entity_decode(clean($testName));
    }
}
