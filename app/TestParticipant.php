<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;
use tcCore\Events\BrowserTestingDisabledForParticipant;
use tcCore\Events\RemoveParticipantFromWaitingRoom;
use tcCore\Events\TestParticipantGuestAvailabilityChanged;
use tcCore\Events\TestTakeForceTakenAway;
use tcCore\Events\TestTakeReopened;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Jobs\Rating\CalculateRatingForTestParticipant;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class TestParticipant extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'                    => EfficientUuid::class,
        'allow_inbrowser_testing' => 'boolean',
        'started_in_new_player'   => 'boolean',
        'available_for_guests'    => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'heartbeat_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_participants';

    protected $appends = ['intense'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_take_id', 'user_id', 'school_class_id', 'test_take_status_id', 'invigilator_note', 'rating', 'allow_inbrowser_testing', 'started_in_new_player', 'answers_provisioned', 'available_for_guests'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public $skipBootSavedMethod = false;

    public static function boot()
    {
        parent::boot();

        static::created(function (TestParticipant $testParticipant) {
            if ($testParticipant->testTake->allow_inbrowser_testing) {
                $testParticipant->allow_inbrowser_testing = true;
                $testParticipant->save();
            }
        });
        static::saved(function (TestParticipant $testParticipant) {
            if ($testParticipant->skipBootSavedMethod) {
                return;
            }
            //$testParticipant->load('testTakeStatus');

            $testParticipant->makeEmptyAnswerOptionsFor();

            $testParticipant->startedTakingTest();
            $testParticipant->reopenedTakingTest();


//            if ($testParticipant->testTakeStatus->name !== 'Taking test' && $testParticipant->getOriginal('test_take_status_id') === 3) {// TestTakeStatus::where('name', '=', 'Taking test')->value('id')) {
            //stoppedTakingTest
            $testParticipant->stoppedTakingTest();
            $testParticipant->updatedRatingOrRetakeRating();

            $testParticipant->isTestTakenAway();
            $testParticipant->isBrowserTestingActive();

            $testParticipant->hasGuestAvailabilityChanged();
        });

        static::deleting(function (TestParticipant $testParticipant) {
            RemoveParticipantFromWaitingRoom::dispatch($testParticipant->uuid);
        });
    }

    private function makeEmptyAnswerOptionsFor()
    {
        // validatie op heartbeat_at was toch niet goed...
//        if (($this->getOriginal('heartbeat_at') === null || $this->getOriginal('heartbeat_at') === '') && $this->test_take_status_id === 3 && $this->answers()->count() === 0) {
        $answersProvisioned = $this->answers_provisioned;
        if ($this->answers()->count() > 0) {
            $answersProvisioned = true;
        }

        if ($this->test_take_status_id === 3 && !$answersProvisioned) {
            $this->load('testTake', 'testTake.test', 'testTake.test.testQuestions', 'testTake.test.testQuestions.question');

            $questions = array();

            foreach ($this->testTake->test->testQuestions as $testQuestion) {
                $question = $testQuestion->question;
                $question->setAttribute('order', $testQuestion->getAttribute('order'));
                $question->setAttribute('maintain_position', $testQuestion->getAttribute('maintain_position'));
                $questions[] = $question;
            }

            usort($questions, function ($a, $b) {
                $a = $a->getAttribute('order');
                $b = $b->getAttribute('order');
                if ($a == $b) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            });

            $order = 1;
            $questionOrder = [];
            $shuffleQuestions = [];
            $availableOrder = [];

            foreach ($questions as $question) {
                if ($question->getAttribute('maintain_position') == 1 || $this->testTake->test->getAttribute('shuffle') == 0) {
                    if (!$question instanceof GroupQuestion && $question instanceof Question) {
                        $question = $question->getQuestionInstance();
                    }
                    $questionOrder[$order] = $question;
                } else {
                    $shuffleQuestions[] = $question;
                    $availableOrder[] = $order;
                }
                $order++;
            }

            //Insert shuffled questions
            shuffle($shuffleQuestions);

            foreach ($shuffleQuestions as $question) {
                $order = array_shift($availableOrder);

                if (!$question instanceof GroupQuestion && $question instanceof Question) {
                    $question = $question->getQuestionInstance();
                }
                $questionOrder[$order] = $question;
            }

            ksort($questionOrder);

            $order = 1;
            $answers = [];
            foreach ($questionOrder as $question) {
                if ($question instanceof GroupQuestion) {
                    $question->generateAnswersForGroupQuestion(array(), $this->testTake->test->getAttribute('shuffle'), $order, $answers);
                } elseif ($question instanceof Question) {
                    $answer = new Answer();
                    $answer->setAttribute('question_id', $question->getKey());
                    $answer->setAttribute('order', $order);

                    $answers[] = $answer;
                    $order++;
                }
            }
            foreach ($answers as $answer) {
                try {
                    $this->answers()->save($answer);
                } catch (\Throwable $e) {
                    // we have an exception probably because of double adding same answer.
                    // so we only are going to send a notification to bugsnag, but won't hold.
                    $body = sprintf('Notice: Error while adding empty answer records for the participant for participant (%d) and question (%d) with error %s', $this->getKey(), $answer->question_id, $e->getMessage());

                    Bugsnag::notifyException(new \LogicException($body));
                }
            }
//            $this->answers()->saveMany($answers);

            (new AnswerParentQuestionsHelper())->fixAnswerParentQuestionsPerTestParticipant($this);
            $this->answers_provisioned = true;
            $this->skipBootSavedMethod = true;
            $this->save();
        }
    }

    public function user()
    {
        return $this->belongsTo('tcCore\User');
    }

    public function testTake()
    {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function testTakeStatus()
    {
        return $this->belongsTo('tcCore\TestTakeStatus');
    }

    public function schoolClass()
    {
        return $this->belongsTo('tcCore\SchoolClass')->withTrashed();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers()
    {
        return $this->hasMany('tcCore\Answer')->orderBy('order');
    }

    public function testTakeEvents()
    {
        return $this->hasMany('tcCore\TestTakeEvent');
    }

    public function getIpAddressAttribute($value)
    {
        if ($value !== null) {
            return inet_ntop($value);
        }
        return $value;
    }

    public function setIpAddressAttribute($value)
    {
        $this->attributes['ip_address'] = inet_pton($value);
    }

    public function getIpAddressInBinary()
    {
        return $this->attributes['ip_address'];
    }

    public function getLabelAttribute()
    {
        return self::getLabelsAndTexts('label', $this->test_take_status_id);
    }

    public function getTextAttribute()
    {
        return self::getLabelsAndTexts('text', $this->test_take_status_id);
    }


    private static function getLabelsAndTexts($type, $value)
    {
        $lookup = [
            1 => [
                'label' => 'info',
                'text'  => 'Ingepland',
            ],
            2 => [
                'label' => 'danger',
                'text'  => 'Niet gemaakt',
            ],
            3 => [
                'label' => 'success',
                'text'  => 'Maakt toets',
            ],
            4 => [
                'label' => 'info',
                'text'  => 'Ingeleverd',
            ],
            5 => [
                'label' => 'warning',
                'text'  => 'Ingeleverd (geforceerd)',
            ],
            6 => [
                'label' => 'success',
                'text'  => 'Ingenomen',
            ],
        ];

        if (!array_key_exists($value, $lookup)) {
            throw new \Exception(sprintf('Couldnot find test_take_status_id %d', $value));
        }

        if (!array_key_exists($type, $lookup[$value])) {
            throw new \Exception(sprintf('Couldnot find %s for test_take_status_id %d', $type, $value));
        }
        return $lookup[$value][$type];
    }

    public function pValues()
    {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings()
    {
        return $this->hasMany('tcCore\Rating');
    }

    private function startedTakingTest()
    {
        //$testTakeStatuses = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken'])->pluck('id')->all();
        $testTakeStatuses = [TestTakeStatus::STATUS_PLANNED, TestTakeStatus::STATUS_TEST_NOT_TAKEN];
        // if ($testParticipant->testTakeStatus->name === 'Taking test' && in_array($testParticipant->getOriginal('test_take_status_id'), $testTakeStatuses)) {
        if ($this->test_take_status_id == TestTakeStatus::STATUS_TAKING_TEST) {
            $testTakeTypeStatus = TestTakeEventType::where('name', '=', 'Start')->value('id');
            $participantStartEvent = TestTakeEvent::whereTestParticipantId($this->getKey())
                                                    ->whereTestTakeId($this->testTake->getKey())
                                                    ->whereTestTakeEventTypeId($testTakeTypeStatus)
                                                    ->first();
            if (!$participantStartEvent) {
                // Test participant test event for starting
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', $testTakeTypeStatus);
                $testTakeEvent->setAttribute('test_participant_id', $this->getKey());
                $this->testTake->testTakeEvents()->save($testTakeEvent);

                $testTakeStartDate = $this->testTake->testTakeEvents()->where('test_take_event_type_id', '=', $testTakeTypeStatus)->whereNull('test_participant_id')->max('created_at');

                $timeLate = Carbon::createFromFormat('Y-m-d H:i:s', $testTakeStartDate)->addMinutes(5);

                if ($timeLate->isPast()) {
                    $testTakeEvent = new TestTakeEvent();
                    $testTakeEvent->setAttribute('test_take_event_type_id',
                        TestTakeEventType::where('name', '=', 'Started late')->value('id'));
                    $testTakeEvent->setAttribute('test_take_id', $this->getAttribute('test_take_id'));

                    $this->testTakeEvents()->save($testTakeEvent);
                }
            }
        }
    }

    private function reopenedTakingTest()
    {
        //            $testTakeStatuses = TestTakeStatus::whereIn('name', ['Handed in', 'Taken away', 'Taken'])->pluck('id')->all();
        $testTakeStatuses = [4, 5, 6];
//            if ($testParticipant->testTakeStatus->name === 'Taking test' && in_array($testParticipant->getOriginal('test_take_status_id'), $testTakeStatuses)) {
        //reopenedTest
        if ($this->test_take_status_id == TestTakeStatus::STATUS_TAKING_TEST && in_array($this->getOriginal('test_take_status_id'), $testTakeStatuses)) {
            // Test participant test event for continueing
            $testTakeEvent = new TestTakeEvent();
            $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Continue')->value('id'));
            $testTakeEvent->setAttribute('test_participant_id', $this->getKey());
            $this->testTake->testTakeEvents()->save($testTakeEvent);
            TestTakeReopened::dispatch($this->uuid);
        }
    }

    private function stoppedTakingTest()
    {
        if ($this->test_take_status_id !== 3 && $this->getOriginal('test_take_status_id') === 3) {// TestTakeStatus::where('name', '=', 'Taking test')->value('id')) {
            // Test participant test event for stopping
            $testTakeEvent = new TestTakeEvent();
            $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Stop')->value('id'));
            $testTakeEvent->setAttribute('test_participant_id', $this->getKey());
            $this->testTake->testTakeEvents()->save($testTakeEvent);
        }
    }

    private function updatedRatingOrRetakeRating()
    {
        if ($this->getAttribute('rating') != $this->getOriginal('rating') || $this->getAttribute('retake_rating') != $this->getOriginal('retake_rating')) {
            $retakeTestTake = $this->testTake->retakeTestTake;
            if ($retakeTestTake !== null) {
                $rating = $this->getOriginal('rating');
                if ($this->getAttribute('rating') != $this->getOriginal('rating')) {
                    $rating = $this->getAttribute('rating');
                }

                $retakeRating = $this->getOriginal('retake_rating');
                if ($this->getAttribute('retake_rating') != $this->getOriginal('retake_rating')) {
                    $retakeRating = $this->getAttribute('retake_rating');
                }

                if ($rating < $retakeRating) {
                    $rating = $retakeRating;
                }

                $retakeTestParticipant = $retakeTestTake->testParticipants()->where('user_id', $this->getAttribute('user_id'))->first();
                if ($retakeTestParticipant !== null) {
                    $retakeTestParticipant->setAttribute('retake_rating', $rating);
                    $retakeTestParticipant->save();
                }
            }

            Queue::push(new CalculateRatingForTestParticipant($this));
        }
    }

    public function getTestTakeUuidAttribute($value)
    {
        return Uuid::fromBytes($value)->toString();
    }

    public function handInTestTake()
    {
        //Remaining handInTestTake actions handled in TestParticipant boot method
        $this->setAttribute('test_take_status_id', TestTakeStatus::STATUS_HANDED_IN)->save();
        return true;
    }

    public function testTakeOpenForInteraction()
    {
        return null !== $this->where('test_take_status_id', TestTakeStatus::STATUS_TAKING_TEST)
                ->orWhere('test_take_status_id', TestTakeStatus::STATUS_DISCUSSING)
                ->first();
    }

    public function getIntenseAttribute()
    {
        if (!$this->user || !$this->user->schoolLocation) {
            return false;
        }
        return $this->user->intense && $this->user->schoolLocation->intense;
    }

    public function canTakeTestTakeInPlayer()
    {
        $statusOkay = $this->test_take_status_id == TestTakeStatus::STATUS_TAKING_TEST;

        if ($statusOkay && $this->testTake->test->isAssignment()) {
            return  ($this->testTake->time_start <= now() && $this->testTake->time_end >= now());
        }
        return $statusOkay;
    }

    private function isTestTakenAway()
    {
        if ($this->test_take_status_id == TestTakeStatus::STATUS_TAKEN && $this->getOriginal('test_take_status_id') == TestTakeStatus::STATUS_TAKING_TEST) {
            TestTakeForceTakenAway::dispatch($this->uuid);
        }
    }

    public function getAlertStatus()
    {
        return false;
    }

    private function isBrowserTestingActive()
    {
        if ($this->allow_inbrowser_testing == false && $this->getOriginal('allow_inbrowser_testing') == true) {
            BrowserTestingDisabledForParticipant::dispatch($this->uuid);
        }
    }

    public function canUseBrowserTesting()
    {
        return $this->allow_inbrowser_testing;
    }

    public function isInBrowser()
    {
        return session('isInBrowser', true);
    }

    public function isRejoiningTestTake($newStatus)
    {
        if ($newStatus === $this->test_take_status_id) {
            $this->testTake->testTakeEvents()->create([
                'test_take_event_type_id' => TestTakeEventType::where('reason', '=', 'rejoined')->value('id'),
                'test_participant_id' => $this->getKey()
            ]);
            return true;
        }
        return false;
    }

    private function hasGuestAvailabilityChanged()
    {
        if ($this->available_for_guests != $this->getOriginal('available_for_guests')) {
            TestParticipantGuestAvailabilityChanged::dispatch($this->testTake->uuid);
        }
    }
}
