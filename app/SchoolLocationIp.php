<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolLocationIp extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_location_ips';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ip', 'netmask'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $submask;

    public static function boot()
    {
        parent::boot();

        static::saving(function(SchoolLocationIp $schoolLocationIp)
        {
            $submask = $schoolLocationIp->getSubmaskString();
            $schoolLocationIp->attributes['ip'] = $schoolLocationIp->attributes['ip'] & $submask;
        });
    }

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    public function getIpAttribute($value) {
        if ($value !== null) {
            return inet_ntop($value);
        }
        return $value;
    }

    public function setIpAttribute($value) {
        $this->attributes['ip'] = inet_pton($value);
    }

    public function setNetmaskAttribute($value) {
        $this->attributes['netmask'] = $value;
        $this->submask = null;
    }

    public function ipInRange($binIp) {
        $submask = $this->getSubmaskString();
        return ($this->attributes['ip'] & $submask) == ($binIp & $submask);
    }

    private function getSubmaskString() {
        if ($this->submask === null) {
            return $this->attributes['ip'];
            // $this->submask = pack('H*', base_convert(str_pad(str_repeat('1', $this->netmask), strlen($this->attributes['ip']) * 8, '0'), 2, 16));
        }
        return $this->submask;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'name':
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
                case 'name':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }


}
