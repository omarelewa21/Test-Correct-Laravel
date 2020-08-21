<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;


class TestTakeEvent extends BaseModel {

    use SoftDeletes;
    use GeneratesUuid;

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

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
