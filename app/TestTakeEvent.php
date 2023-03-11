<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Auth;
use tcCore\Events\NewTestTakeEventAdded;
use tcCore\Events\RemoveFraudDetectionNotification;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Http\Enums\VirtualMachineDetectionTypes;
use tcCore\Http\Enums\VirtualMachineSoftwares;
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
            $testTakeEvent = self::handleMetadata($testTakeEvent);

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

    public static function hasFraudBeenDetectedForParticipant($participantId, $showAlarmToStudent = true)
    {
        $query = self::leftJoin('test_take_event_types', 'test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('confirmed', 0)
            ->where('test_participant_id', $participantId)
            ->where('requires_confirming', 1);
        
        if ($showAlarmToStudent) {
            $query = $query->where('show_alarm_to_student', 1);
        }
            
        return (bool)$query->count();
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

    private static function handleMetadata(TestTakeEvent $testTakeEvent): TestTakeEvent {
        if ($testTakeEvent->testTakeEventType->reason == "vm") {
            try {
                $metadata = $testTakeEvent->metadata;
                
                if (property_exists($metadata, 'software') && is_int($metadata['software'])) {
                    // the reported software is an integer so through the HID detection
                    //.we translate it to a string
                    switch ($metadata['software']) {
                        case 0x15ad:
                            $metadata['software'] = VirtualMachineSoftwares::vmware;
                            break;
                        case 0x0e0f:
                            $metadata['software'] = VirtualMachineSoftwares::vmware;
                            break;
                        case 0x80ee:
                            $metadata['software'] = VirtualMachineSoftwares::virtualbox;
                            break;
                        case 0x203a:
                            $metadata['software'] = VirtualMachineSoftwares::parallels;
                            break;
                        case 0x46f4:
                            $metadata['software'] = VirtualMachineSoftwares::qemu;
                            break;
                        default:
                            $metadata['software'] = VirtualMachineSoftwares::unknown . ', vendor: ' . $metadata['software'];
                            break;
                    }
                } elseif (property_exists($metadata, 'type') && $metadata['type'] == VirtualMachineDetectionTypes::windows) {
                    // the reported software is through the Pafish VM detection
                    if (property_exists($metadata, 'vmware') &&
                        (
                            $metadata['vmware']['scsi'] === true || 
                            $metadata['vmware']['registry'] === true ||
                            $metadata['vmware']['mouseDriver'] === true ||
                            $metadata['vmware']['graphicsDriver'] === true ||
                            $metadata['vmware']['macAddress'] === true ||
                            $metadata['vmware']['devices'] === true ||
                            $metadata['vmware']['wmiSerial'] === true
                        )
                    ) {
                        $metadata['software'] = VirtualMachineSoftwares::vmware;
                    } else if (property_exists($metadata, 'virtualbox') &&
                        (
                            $metadata['virtualbox']['scsi'] === true || 
                            $metadata['virtualbox']['biosVersion'] === true ||
                            $metadata['virtualbox']['guestAdditions'] === true ||
                            $metadata['virtualbox']['videoBiosVersion'] === true ||
                            $metadata['virtualbox']['acpi'] === true ||
                            $metadata['virtualbox']['fadtAcpi'] === true ||
                            $metadata['virtualbox']['rsdtAcpi'] === true ||
                            $metadata['virtualbox']['service'] === true ||
                            $metadata['virtualbox']['systemBiosDate'] === true ||
                            $metadata['virtualbox']['deviceDrivers'] === true ||
                            $metadata['virtualbox']['systemFiles'] === true ||
                            $metadata['virtualbox']['nicMacAddress'] === true ||
                            $metadata['virtualbox']['devices'] === true ||
                            $metadata['virtualbox']['trayWindow'] === true ||
                            $metadata['virtualbox']['sharedNetwork'] === true ||
                            $metadata['virtualbox']['processes'] === true ||
                            $metadata['virtualbox']['wmi'] === true
                        )
                    ) {
                        $metadata['software'] = VirtualMachineSoftwares::virtualbox;
                    } else if (property_exists($metadata, 'qemu') &&
                        (
                            $metadata['qemu']['scsi'] === true || 
                            $metadata['qemu']['systemBios'] === true ||
                            $metadata['qemu']['cpuBrand'] === true
                        )
                    ) {
                      $metadata['software'] = VirtualMachineSoftwares::qemu;
                    } else if (property_exists($metadata, 'wine') &&
                        (
                            $metadata['wine']['unixFileName'] === true || 
                            $metadata['wine']['systemBios'] === true ||
                            $metadata['wine']['cpuBrand'] === true
                        )
                    ) {
                        $metadata['software'] = VirtualMachineSoftwares::wine;
                    } else if (property_exists($metadata, 'sandboxie') &&
                        (
                            $metadata['sandboxie']['dll'] === true
                        )
                    ) {
                        $metadata['software'] = VirtualMachineSoftwares::sandboxie;
                    } else if (property_exists($metadata, 'cpuInfo') &&
                        (
                            $metadata['cpuInfo']['knownVMVendor'] === true ||
                            $metadata['cpuInfo']['hv_bit'] === true
                        )
                    ) {
                        $metadata['software'] = VirtualMachineSoftwares::unknown . ', vendor: '
                            . $metadata['vendor'] . ' & Hypervisor: ' . $metadata['hypervisorVendor'];
                    }
                } elseif (property_exists($metadata, 'type') && $metadata['type'] == VirtualMachineDetectionTypes::macos) {
                  // TODO: implement macOS VM detection in the client and a parser here  
                } else {
                    Bugsnag::leaveBreadcrumb('metadata', 'info', $metadata);
                    Bugsnag::notifyError("Could not handle VM detection fraud event", "Failed to parse metadata");
                    
                }
                $testTakeEvent->metadata = $metadata;
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }
        }

        return $testTakeEvent;
    }
}
