<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileManagement extends BaseModel {

    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at','updated_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file_managements';

//    /**
//     * The attributes that are mass assignable.
//     *
//     * @var array
//     */
//    protected $fillable = ['*'];

    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation', 'school_location_id');
    }

    public function handler() {
        return $this->belongsTo('tcCore\User','handledby');
    }

    public function status(){
        return $this->belongsTo('tcCore\FileManagementStatus','file_management_status_id');
    }

    public function children(){
        return $this->hasMany('tcCore\FileManagement','parent_id');
    }

}
