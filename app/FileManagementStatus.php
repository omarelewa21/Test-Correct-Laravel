<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManagementStatus extends BaseModel {

    use SoftDeletes;

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
}
