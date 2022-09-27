<?php namespace tcCore;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use LaravelIdea\Helper\tcCore\_IH_SchoolClass_QB;
use Ramsey\Uuid\Uuid;
use tcCore\Events\InbrowserTestingUpdatedForTestParticipant;
use tcCore\Events\NewTestTakeGraded;
use tcCore\Events\NewTestTakeReviewable;
use tcCore\Events\TestParticipantEvent;
use tcCore\Events\TestTakeOpenForInteraction;
use tcCore\Events\TestTakeShowResultsChanged;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\GlobalStateHelper;
use tcCore\Http\Helpers\TestTakeCodeHelper;
use tcCore\Jobs\CountTeacherLastTestTaken;
use tcCore\Jobs\CountTeacherTestDiscussed;
use tcCore\Jobs\CountTeacherTestTaken;
use tcCore\Jobs\SendTestPlannedMail;
use tcCore\Jobs\SendTestRatedMail;
use tcCore\Lib\Answer\AnswerChecker;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Jobs\SendExceptionMail;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\TestParticipant\Factory;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Scopes\ArchivedScope;
use tcCore\Traits\Archivable;
use tcCore\Traits\UuidTrait;
use Illuminate\Support\Str;

class TestTake extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;
    use Archivable;

    protected $casts = [
        'uuid'            => EfficientUuid::class,
        'notify_students' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'time_start', 'time_end', 'show_results', 'exported_to_rtti'];

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
    protected $fillable = ['test_id', 'test_take_status_id', 'period_id', 'retake', 'retake_test_take_id', 'time_start', 'time_end', 'location', 'weight', 'note', 'invigilator_note', 'show_results', 'discussion_type', 'is_rtti_test_take', 'exported_to_rtti', 'allow_inbrowser_testing', 'guest_accounts', 'skipped_discussion', 'notify_students', 'user_id', 'scheduled_by'];

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

    protected $appends = ['exported_to_rtti_formated','invigilators_acceptable','invigilators_unacceptable_message', 'directLink'];

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

            if ($testTake->testTakeStatus->name === 'Discussing' && $testTake->getAttribute('discussing_question_id') != $testTake->getOriginal('discussing_question_id')) {
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
            if($testTake->getOriginal('test_take_status_id') != $testTake->test_take_status_id) {
                TestTakeStatusLog::create([
                    'test_take_id' => $testTake->getKey(),
                    'test_take_status_id' => $testTake->test_take_status_id
                ]);
                // if we go from taken to discussed without actual discussing, we get a record created 8 (but in the mean time a 7 is also created as
                // initiated from the frontend, so we need to remove that record if it was in the last 60 seconds as then you did not really discuss the test take
                if((int) $testTake->test_take_status_id === 8){
                    TestTakeStatusLog::where('test_take_id',$testTake->getKey())->where('test_take_status_id',7)->where('created_at','>=',Carbon::now()->subSeconds(120))->delete();
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

                $heartbeatDate = Carbon::now();
                $heartbeatDate->subSeconds(30);

                //test_take_status_id is the ID of 'Taking test'
                $testParticipantTestTakeStatus = $testTake->getAttribute('test_take_status_id');

                $testNotTakenId = TestTakeStatus::where('name', 'Test not taken')->value('id');

                $testTake->load('testParticipants', 'testParticipants.schoolClass', 'testParticipants.schoolClass.schoolLocation');
                foreach ($testTake->testParticipants as $testParticipant) {
                    // If school location of the test participant is not activated, do not allow switching to state Taking test of Discussing.
                    $activated = $testParticipant->schoolClass->schoolLocation->getAttribute('activated');

                    //Disabled setting TestParticipant status to 3 if there is an recent heartbeat.
                    // As this is done by the testparticipant actively the moment the student chooses a player
                    //-Roan 20211008
//                    if ($activated == true && $testParticipant->getAttribute('heartbeat_at') !== null && $testParticipant->getAttribute('heartbeat_at') >= $heartbeatDate) {
//                        $testParticipant->setAttribute('test_take_status_id', $testParticipantTestTakeStatus);
//                    } else {
//                        $testParticipant->setAttribute('test_take_status_id', $testNotTakenId);
//                    }
                    $testParticipant->setAttribute('test_take_status_id', $testNotTakenId);

                    $testParticipant->save();

                    TestTakeOpenForInteraction::dispatch($testParticipant->uuid);
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

            if ($testTake->testTakeStatus->name === 'Discussing' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id')) {
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Start discussion')->value('id'));

                $testTake->testTakeEvents()->save($testTakeEvent);

                $heartbeatDate = Carbon::now();
                $heartbeatDate->subSeconds(30);

                //test_take_status_id is the ID of 'Discussing'
                $testParticipantDiscussingStatus = $testTake->getAttribute('test_take_status_id');

                $testTakeDiscussionNotAllowedStatusses = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken', 'Taken away'])->pluck('id', 'name')->all();

                $testTake->load([   'testParticipants',
                                    'testParticipants.schoolClass' => function($query){
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
                    TestTakeOpenForInteraction::dispatch($testParticipant->uuid);
                }
            }

            if (($testTake->testTakeStatus->name === 'Discussing' && $testTake->getAttribute('discussing_question_id') != $testTake->getOriginal('discussing_question_id'))
                || ($testTake->testTakeStatus->name === 'Discussed' && $testTake->getAttribute('test_take_status_id') != $testTake->getOriginal('test_take_status_id'))) {
                $inactiveTestParticipant = [];
                $testTakeDiscussedStatus = TestTakeStatus::where('name', 'Discussing')->value('id');
                foreach ($testTake->testParticipants as $testParticipant) {
                    if ($testTakeDiscussedStatus != $testParticipant->getAttribute('test_take_status_id')) {
                        $inactiveTestParticipant[] = $testParticipant->getAttribute('user_id');
                    }

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
                if(GlobalStateHelper::getInstance()->isQueueAllowed()) {
                    Queue::later(300, new SendTestRatedMail($testTake));
                }

                $testTake->testParticipants->each(function($participant) {
                    NewTestTakeGraded::dispatch($participant->user()->value('uuid'));
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

        static::creating(function(TestTake $testTake) {
            if($testTake->school_location_id === null) {
                $testTake->school_location_id = Auth::user()->school_location_id;
            }
            $testTake->scheduled_by = auth()->id();
        });

        static::created(function (TestTake $testTake) {

            if ($testTake->schoolClasses !== null) {
                $testTake->saveSchoolClassTestTakeParticipants();
            }
            if($testTake->notify_students && GlobalStateHelper::getInstance()->isQueueAllowed()) {
                logger('lekker mailen');
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

    public function isAllowedToView(User $userToCheck)
    {

        $value = count(DB::select("select
        `test_take_id`
      from
        `test_participants`
      where
        deleted_at is null AND
        `school_class_id` in (
            select
            `class_id`
          from
            `teachers`
          where
            `user_id` = :userId AND
            deleted_at is null
        ) and test_take_id = :testTakeId",
            ['userId' => $userToCheck->getKey(), 'testTakeId' => $this->getKey()]
        ));

        return ($value > 0 && $userToCheck->hasAccessToTest($this->test)) || $this->isInvigilator($userToCheck) || $this->isScheduledByUser($userToCheck);
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
    }

    private function saveInvigilators()
    {
        $invigilators = $this->invigilators()->withTrashed()->get();

        $this->syncTcRelation($invigilators, $this->invigilators, 'user_id', function ($takeTake, $invigilator) {
            Invigilator::create(['user_id' => $invigilator, 'test_take_id' => $takeTake->getKey()]);
        });

        $this->invigilators = null;
    }

    public function saveSchoolClassTestTakeParticipants()
    {
        $testTakeParticipantFactory = new Factory(new TestParticipant());
        $testParticipants = $testTakeParticipantFactory->generateMany($this->getKey(), ['school_class_ids' => $this->schoolClasses, 'test_take_status_id' => with(TestTakeStatus::where('name', 'Planned')->first())->getKey()]);
//logger(print_r($testParticipants,true));
        $this->testParticipants()->saveMany($testParticipants);
        $this->schoolClasses = null;
    }



    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = $this->getUserRoles();
        /** todo: uitzoeken waar het scenario en Teacher en Student overgaat */
        if (in_array('Teacher', $roles) && in_array('Student', $roles)) {
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
            $query->accessForTeacher($user)
                ->withoutDemoTeacherForUser($user)
                ->onlyTestsFromSubjectsOrIfDemoThenOnlyWhenOwner($user)
                ->when($user->isValidExamCoordinator(), fn($query) => $query->scheduledByExamCoordinator($user));

        } elseif (in_array('Student', $roles)) {
            $query->whereIn($this->getTable() . '.id', function ($query) {
                $query->select('test_take_id')
                    ->from(with(new TestParticipant())->getTable())
                    ->where('user_id', Auth::id())
                    ->where('deleted_at', null);
            });
        }

        $query->belongsToSchoolLocation(Auth::user());
        //$query->where($this->getTable().'.school_location_id', Auth::user()->school_location_id);

        $testTable = with(new Test())->getTable();
        $query->select($this->getTable() . '.*')
            ->join($testTable, $testTable . '.id', '=', $this->getTable() . '.test_id');

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'type_not_assessment':
                    $query->typeNotAssessment();
                    break;
                case 'takeUuid':
                    $query->where('test_takes.id', Self::whereUuid($value)->value('id'));
                    break;
                case 'type_assessment':
                    $query->typeAssessment();
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
                    $query->whereIn($this->getTable().'.period_id', $value);
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
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    $query->whereIn('test_take_status_id', $value);
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
                        $query->whereIn($this->getTable() . '.id', TestParticipant::whereIn('school_class_id', $value)->distinct()->pluck('test_take_id'));
                    } else {
                        $query->whereIn($this->getTable() . '.id', TestParticipant::where('school_class_id', $value)->distinct()->pluck('test_take_id'));
                    }
                    break;
                case 'school_class_name':
                    $query->whereIn(
                        $this->getTable().'.id',
                        TestParticipant::whereHas('schoolClass', function ($q) use ($value) {
                            $q->where('name', 'LIKE', '%'.$value.'%');
                        })->distinct()
                            ->pluck('test_take_id'));
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
                            ->when(is_int($value),
                                fn($query) => $query->where('tests.subject_id', $value),
                                fn($query) => $query->whereIn('tests.subject_id', $value)
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
    public function isInvigilator(User $userToCheck)
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

    public function scopeNotDemo($query, $tableAlias=null)
    {
        if (!$tableAlias) {
            $tableAlias = $this->getTable();
        }

        return $query->where(sprintf('%s.demo', $tableAlias), 0);
    }

    public function hasCarousel()
    {
        $countCarouselGroupsInTestTake = GroupQuestion::whereIn('id',
            $this->test->testQuestions->pluck('question_id')
        )->where('groupquestion_type' ,'carousel')->count();

        return $countCarouselGroupsInTestTake > 0;
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
        return array_key_exists('exported_to_rtti',$this->attributes) && $this->attributes['exported_to_rtti'] ? Carbon::parse($this->attributes['exported_to_rtti'])->format('d-m-Y H:i:s') : 'Nog niet geëxporteerd';
    }

    public function getInvigilatorsAcceptableAttribute()
    {
        if($this->hasValidInvigilators()){
            return true;
        }
        return false;
        if($this->hasRemovedInvigilators()){
            $invigilatorsRemoved = true;
        }
    }

    public function getInvigilatorsUnacceptableMessageAttribute()
    {
        if($this->hasValidInvigilators()){
            return '';
        }
        if($this->hasRemovedInvigilators()){
            return __('De surveilant is niet langer actief binnen Test-Correct');
        }
        return _('Er is geen surveillant gekoppeld');
    }

    public function getDirectLinkAttribute(){
        return config('app.base_url') ."directlink/". $this->uuid;
    }

    private function createTestTakeCodeIfNeeded()
    {
        if ($this->testTakeCode()->count() === 0) {
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
        $this->testParticipants->each(function($participant) {
            if ($participant->user()->value('guest') == true) {
                $participant->available_for_guests = true;
                $participant->save();
            }
        });
    }

    private function handleInbrowserTestingChangesForParticipants()
    {
        if ($this->allow_inbrowser_testing != $this->getOriginal('allow_inbrowser_testing')) {
            TestParticipant::where('test_take_id', $this->getKey())
                ->get()
                ->each(function ($participant) {
                    $participant->setAttribute('allow_inbrowser_testing', $this->allow_inbrowser_testing)->save();
                    InbrowserTestingUpdatedForTestParticipant::dispatch($participant->uuid);
                });
        }
    }

    private function handleShowResultChanges()
    {
        if ($this->show_results != $this->getOriginal('show_results')) {
            TestTakeShowResultsChanged::dispatch($this->uuid);

            $this->testParticipants->each(function($participant) {
                NewTestTakeReviewable::dispatch($participant->user()->value('uuid'));
            });
        }
    }

    private function orUserHasAccessToSchoolClassParticipantsAndSubjectScope($query, User $user)
    {
        $query->orWhereIn($this->getTable().'.id', function ($query) use ($user) {
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
                ->whereIn($this->getTable().'.id',
                    function ($query) use ($teacherTable, $schoolClassTable, $currentSchoolYearId) {
                        $testTable = with(new Test())->getTable();
                        $query
                            ->select($this->getTable().'.id')
                            ->from($this->getTable())
                            ->join($testTable, $testTable.'.id', '=', $this->getTable().'.test_id')
                            ->whereNull($testTable.'.deleted_at')
                            ->whereIn($testTable.'.subject_id',
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

    private function orUserIsInvigilatorScope($query, User $user)
    {
        $query->orWhereIn($this->getTable().'.id', function ($query) use ($user) {
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

    public function scopeAccessForTeacher($query, User $user)
    {
        $query->where(function ($query) use ($user) {
            $this
                ->orUserIsCreatorScope($query, $user)
                ->orUserIsInvigilatorScope($query, $user)
                ->orUserHasAccessToSchoolClassParticipantsAndSubjectScope($query, $user);
        });
    }

    public function scopeBelongsToSchoolLocation($query, User $user)
    {
        $query->where($this->getTable().'.school_location_id', $user->school_location_id);
    }

    public function scopeWithoutDemoTeacherForUser($query, User $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where(function ($query) use ($user) {
                $query->where($this->getTable().'.demo', 1)
                    ->where($this->getTable().'.user_id', $user->getKey());
            })->orWhere($this->getTable().'.demo', 0);
        });
    }

    public function scopeOnlyTestsFromSubjectsOrIfDemoThenOnlyWhenOwner($query, User $user)
    {
        $query->where(function ($q) use ($user) {
            $subject = (new DemoHelper())->getDemoSubjectForTeacher($user);
            //TCP-156
            if ($subject === null) {
                if (config('app.url_login') == "https://testportal.test-correct.nl/" || config('app.url_login') == "https://testportal.test-correct.nl/" || config('app.env') == "production") {
                    dispatch(new SendExceptionMail("Er is iets mis met de demoschool op ".config('app.url_login')."! \$subject is null in TestTake.php. Dit betekent dat docenten toetsen van andere docenten kunnen zien. Dit moet zo snel mogelijk opgelost worden!",
                        __FILE__, 510, []));
                }
                return;
            }

            $q->whereIn($this->getTable().'.id', function ($query) use ($subject, $user) {
                $testTable = with(new Test())->getTable();
                $query
                    ->select($this->getTable().'.id')
                    ->from($this->getTable())
                    ->join($testTable, $testTable.'.id', '=', $this->getTable().'.test_id')
                    ->whereNull($testTable.'.deleted_at')
                    ->where(function ($query) use ($subject, $user, $testTable) {
                        $query->where(function ($query) use ($testTable, $subject, $user) {
                            $query->where($testTable.'.subject_id', $subject->getKey())
                                ->where($testTable.'.author_id', $user->getKey());
                        })
                            ->orWhere($testTable.'.subject_id', '<>', $subject->getKey());
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
        foreach ($this->invigilatorUsers as $invigilator){
            if(is_null($invigilator->deleted_at)){
                return true;
            }
        }
        return false;
    }

    private function hasRemovedInvigilators(){
        foreach ($this->invigilatorUsers as $invigilator){
            if(!is_null($invigilator->deleted_at)){
                return true;
            }
        }
        return false;
    }

    public function scopeTypeAssessment(Builder $query)
    {
        return $query->when(
            !self::isJoined($query, 'tests'),
            function($query) {
                $query->join('tests', 'test_takes.test_id', 'tests.id');
            }
        )->where('tests.test_kind_id', TestKind::ASSESSMENT_TYPE);
    }

    public function scopeTypeNotAssessment(Builder $query)
    {
        return $query->when(
            !self::isJoined($query, 'tests'),
            function($query) {
                $query->join('tests', 'test_takes.test_id', 'tests.id');
            }
        )->where('test_kind_id', '<>', TestKind::ASSESSMENT_TYPE);
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
                    ->where('time_end','>',now());

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

    public function maxScore($ignoreQuestions = []){
        foreach ($ignoreQuestions as $key=>$value){
            if(!strstr($value,'.')){
                continue;
            }
            $arr = explode('.',$value);
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

    public static function redirectToDetailPage($testTakeUuid)
    {
        $detailUrl = sprintf('test_takes/view/%s', $testTakeUuid);
        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('page', $detailUrl, auth()->user());

        return redirect($temporaryLogin->createCakeUrl());
    }

    public function getParticipantTakenStats()
    {
        $this->loadMissing('testParticipants');
        return [
            'taken'    => $this->testParticipants->filter(function ($participant) {
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
}
