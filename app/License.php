<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\CountSchoolLocationActiveLicenses;
use tcCore\Jobs\CountSchoolLocationExpiredLicenses;
use tcCore\Jobs\CountSchoolLocationLicenses;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class License extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'licenses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['start', 'end', 'amount'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function(License $license)
        {
            $license->dispatchJobs();

            // logging amount difference if changed
            if ($license->getOriginal('amount') != $license->amount) {
                LicenseLog::create([
                    'license_id' => $license->getKey(),
                    'amount' => $license->amount,
                    'amount_change' => (int) ($license->amount - $license->getOriginal('amount'))
                ]);
            }
        });

        static::deleted(function(License $license)
        {
            $license->dispatchJobs(true);
        });
    }

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    protected function dispatchJobs($isDeleted = false) {
        $schoolLocation = $this->schoolLocation;

        if ($schoolLocation !== null) {
            Queue::push(new CountSchoolLocationActiveLicenses($schoolLocation));
            Queue::push(new CountSchoolLocationExpiredLicenses($schoolLocation));
            Queue::push(new CountSchoolLocationLicenses($schoolLocation));
        }
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'start_from':
                    $query->where('start', '>=', $value);
                    break;
                case 'start_to':
                    $query->where('start', '<=', $value);
                    break;
                case 'end_from':
                    $query->where('end', '>=', $value);
                    break;
                case 'end_to':
                    $query->where('end', '<=', $value);
                    break;
                case 'amount_below':
                    $query->where('amount', '<', $value);
                    break;
                case 'amount_above':
                    $query->where('amount', '>=', $value);
                    break;
                case 'amount':
                    $query->where('amount', $value);
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'start':
                case 'end':
                case 'amount':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch(strtolower($key)) {
                case 'id':
                case 'start':
                case 'end':
                case 'amount':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }


}
