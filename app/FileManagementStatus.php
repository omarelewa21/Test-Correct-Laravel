<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManagementStatus extends BaseModel {

    use SoftDeletes;

    const STATUS_PROVIDED = 14;
    const STATUS_CANCELLED = 13;
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at','updated_at'];

    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function files() {
        return $this->hasMany('tcCore\FileManagement');
    }

    public function parent() {
        return $this->belongsTo('tcCore\FileManagementStatus','partof');
    }

    public function scopeForToetsenbakkers($query)
    {
        return $query->where('id', '<>', FileManagementStatus::STATUS_PROVIDED);
    }
}
