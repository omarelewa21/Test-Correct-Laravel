<?php namespace tcCore;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\Rating\CalculateRatingForTestParticipant;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestParticipant extends BaseModel {

    use SoftDeletes;

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_take_id', 'user_id', 'school_class_id', 'test_take_status_id', 'invigilator_note', 'rating'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::saved(function(TestParticipant $testParticipant)
        {
            $testParticipant->load('testTakeStatus');

            if ($testParticipant->testTakeStatus->name === 'Taking test' && $testParticipant->answers()->count() === 0) {
                $testParticipant->load('testTake', 'testTake.test', 'testTake.test.testQuestions', 'testTake.test.testQuestions.question');

                $questions = array();

                foreach($testParticipant->testTake->test->testQuestions as $testQuestion) {
                    $question = $testQuestion->question;
                    $question->setAttribute('order', $testQuestion->getAttribute('order'));
                    $question->setAttribute('maintain_position', $testQuestion->getAttribute('maintain_position'));
                    $questions[] = $question;
                }

                usort($questions, function($a, $b) {
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

                foreach($questions as $question) {
                    if ($question->getAttribute('maintain_position') == 1 || $testParticipant->testTake->test->getAttribute('shuffle') == 0) {
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

                foreach($shuffleQuestions as $question) {
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
                        $question->generateAnswersForGroupQuestion(array(), $testParticipant->testTake->test->getAttribute('shuffle'), $order, $answers);
                    } elseif ($question instanceof Question) {
                        $answer = new Answer();
                        $answer->setAttribute('question_id', $question->getKey());
                        $answer->setAttribute('order', $order);

                        $answers[] = $answer;
                        $order++;
                    }
                }
                $testParticipant->answers()->saveMany($answers);
            }

            $testTakeStatuses = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken'])->lists('id')->all();
            if ($testParticipant->testTakeStatus->name === 'Taking test' && in_array($testParticipant->getOriginal('test_take_status_id'), $testTakeStatuses)) {
                // Test participant test event for starting
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Start')->value('id'));
                $testTakeEvent->setAttribute('test_participant_id', $testParticipant->getKey());
                $testParticipant->testTake->testTakeEvents()->save($testTakeEvent);

                $testTakeTypeStatus = TestTakeEventType::where('name', '=', 'Start')->value('id');
                $testTakeStartDate = $testParticipant->testTake->testTakeEvents()->where('test_take_event_type_id', '=', $testTakeTypeStatus)->whereNull('test_participant_id')->max('created_at');
                $timeLate = Carbon::createFromFormat('Y-m-d H:i:s', $testTakeStartDate)->addMinutes(5);

                if ($timeLate->isPast()) {
                    $testTakeEvent = new TestTakeEvent();

                    $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Started late')->value('id'));
                    $testTakeEvent->setAttribute('test_take_id', $testParticipant->getAttribute('test_take_id'));

                    $testParticipant->testTakeEvents()->save($testTakeEvent);
                }
            }

            $testTakeStatuses = TestTakeStatus::whereIn('name', ['Handed in', 'Taken away', 'Taken'])->lists('id')->all();
            if ($testParticipant->testTakeStatus->name === 'Taking test' && in_array($testParticipant->getOriginal('test_take_status_id'), $testTakeStatuses)) {
                // Test participant test event for continueing
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Continue')->value('id'));
                $testTakeEvent->setAttribute('test_participant_id', $testParticipant->getKey());
                $testParticipant->testTake->testTakeEvents()->save($testTakeEvent);
            }

            if ($testParticipant->testTakeStatus->name !== 'Taking test' && $testParticipant->getOriginal('test_take_status_id') === TestTakeStatus::where('name', '=', 'Taking test')->value('id')) {
                // Test participant test event for stopping
                $testTakeEvent = new TestTakeEvent();
                $testTakeEvent->setAttribute('test_take_event_type_id', TestTakeEventType::where('name', '=', 'Stop')->value('id'));
                $testTakeEvent->setAttribute('test_participant_id', $testParticipant->getKey());
                $testParticipant->testTake->testTakeEvents()->save($testTakeEvent);
            }

            if ($testParticipant->getAttribute('rating') != $testParticipant->getOriginal('rating') || $testParticipant->getAttribute('retake_rating') != $testParticipant->getOriginal('retake_rating')) {
                $retakeTestTake = $testParticipant->testTake->retakeTestTake;
                if ($retakeTestTake !== null) {
                    $rating = $testParticipant->getOriginal('rating');
                    if ($testParticipant->getAttribute('rating') != $testParticipant->getOriginal('rating')) {
                        $rating = $testParticipant->getAttribute('rating');
                    }

                    $retakeRating = $testParticipant->getOriginal('retake_rating');
                    if ($testParticipant->getAttribute('retake_rating') != $testParticipant->getOriginal('retake_rating')) {
                        $retakeRating = $testParticipant->getAttribute('retake_rating');
                    }

                    if ($rating < $retakeRating) {
                        $rating = $retakeRating;
                    }

                    $retakeTestParticipant = $retakeTestTake->testParticipants()->where('user_id', $testParticipant->getAttribute('user_id'))->first();
                    if ($retakeTestParticipant !== null) {
                        $retakeTestParticipant->setAttribute('retake_rating', $rating);
                        $retakeTestParticipant->save();
                    }
                }

                Queue::push(new CalculateRatingForTestParticipant($testParticipant));
            }
        });
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function testTake() {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function testTakeStatus() {
        return $this->belongsTo('tcCore\TestTakeStatus');
    }

    public function schoolClass() {
        return $this->belongsTo('tcCore\SchoolClass');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function answers() {
        return $this->hasMany('tcCore\Answer');
    }

    public function testTakeEvents() {
        return $this->hasMany('tcCore\TestTakeEvent');
    }

    public function getIpAddressAttribute($value) {
        if ($value !== null) {
            return inet_ntop($value);
        }
        return $value;
    }

    public function setIpAddressAttribute($value) {
        $this->attributes['ip_address'] = inet_pton($value);
    }

    public function getIpAddressInBinary() {
        return $this->attributes['ip_address'];
    }

    public function pValues() {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings() {
        return $this->hasMany('tcCore\Rating');
    }
}
