<?php

namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class TrialPeriod extends Model
{
    use SoftDeletes, UuidTrait;

    protected $casts = [
        'uuid'             => EfficientUuid::class,
        'deleted_at'       => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'trial_until'      => 'datetime',
        'trial_started_at' => 'datetime',
    ];

    protected $fillable = ['user_id', 'trial_until','school_location_id', 'trial_started_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (TrialPeriod $trialPeriod) {
            $trialPeriod->trial_until = Carbon::now()->startOfDay()->addDays(config('custom.default_trial_days'));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schoolLocation()
    {
        return $this->belongsTo(SchoolLocation::class, 'school_location_id');
    }

    public function scopeWithSchoolLocation($query, SchoolLocation $schoolLocation)
    {
        return $query->where('school_location_id', $schoolLocation->getKey());
    }
}