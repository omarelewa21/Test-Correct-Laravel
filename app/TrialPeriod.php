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

    protected $casts = ['uuid' => EfficientUuid::class];

    protected $dates = ['created_at','updated_at', 'deleted_at', 'trial_until'];
    protected $fillable = ['user_id', 'trial_until','school_location_id'];

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
}