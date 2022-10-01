<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Auth;
use tcCore\Events\NewTestTakeEventAdded;
use tcCore\Events\RemoveFraudDetectionNotification;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;


class TestTakeEvent extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'metadata' => 'array'
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
    protected $table = 'test_take_events';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_participant_id', 'test_take_event_type_id', 'confirmed'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::saving(function(TestTakeEvent $testTakeEvent) {
            if ($testTakeEvent->testTakeEventType->reason == "vm") {
                try {
                    $metadata = $testTakeEvent->metadata;
                    switch ($metadata['software']) {
                        case 0x15ad:
                            $metadata['software'] = 'VMWare';
                            break;
                        case 0x0e0f:
                            $metadata['software'] = 'VMWare';
                            break;
                        case 0x80ee:
                            $metadata['software'] = 'Virtualbox';
                            break;
                        case 0x203a:
                            $metadata['software'] = 'Parallels';
                            break;
                        case 0x46f4:
                            $metadata['software'] = 'QEMU';
                            break;
                        default:
                            $metadata['software'] = '???, vendor: ' . $metadata['software'];
                            break;
                    }
                    $testTakeEvent->metadata = $metadata;
                } catch (\Throwable $th) {
                    Bugsnag::notifyException($th);
                }

            }

            if ($testTakeEvent->shouldIgnoreEventRegistration()) {
                return false;
            }
        });

        static::created(function(TestTakeEvent $testTakeEvent) {
            NewTestTakeEventAdded::dispatch($testTakeEvent->testTake->uuid);
        });

        static::saved(function(TestTakeEvent $testTakeEvent) {
            if ($testTakeEvent->confirmed == 1 && $testTakeEvent->getOriginal('confirmed') == 0) {
                RemoveFraudDetectionNotification::dispatch($testTakeEvent->testParticipant->uuid);
            }
        });
    }

    public function testTakeEventType() {
        return $this->belongsTo('tcCore\TestTakeEventType');
    }

    public function testTake() {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function testParticipant() {
        return $this->belongsTo('tcCore\TestParticipant');
    }

    public function scopeFiltered($query, $filters = [], $sorting = []) {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'id':
                    if (is_array($value)) {
                        $query->whereIn('id', $value);
                    } else {
                        $query->where('id', '=', $value);
                    }
                    break;
                case 'test_take_id':
                    if (is_array($value)) {
                        $query->whereIn('test_take_id', $value);
                    } else {
                        $query->where('test_take_id', '=', $value);
                    }
                    break;
                case 'test_participant_id':
                    if (is_array($value)) {
                        $query->whereIn('test_participant_id', $value);
                    } else {
                        $query->where('test_participant_id', '=', $value);
                    }
                    break;
                case 'test_take_event_type_id':
                    if (is_array($value)) {
                        $query->whereIn('test_take_event_type_id', $value);
                    } else {
                        $query->where('test_take_event_type_id', '=', $value);
                    }
                    break;
                case 'confirmed':
                    if (is_array($value)) {
                        $query->whereIn('confirmed', $value);
                    } else {
                        $query->where('confirmed', '=', $value);
                    }
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'created_at':
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
                case 'created_at':
                    $query->orderBy($key, $value);
                    break;
            }
        }
    }

    public static function hasFraudBeenDetectedForParticipant($participantId)
    {
        // Only returns true if the fraud should also be visible to the student.
        // There are situations where the fraud should only be visible to the teacher, which are not included in this function
        return !!self::leftJoin('test_take_event_types', 'test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('confirmed', 0)
            ->where('test_participant_id', $participantId)
            ->where('requires_confirming', 1)
            ->where('show_alarm_to_student', 1)
            ->count();
    }

    public function shouldIgnoreEventRegistration()
    {
        if ($this->testTake->test->isAssignment()){
            if ($this->testTakeEventType->requires_confirming == 1) {
                return true;
            }
        }

        return false;
    }
}
