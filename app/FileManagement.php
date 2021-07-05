<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class FileManagement extends BaseModel {

    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at','updated_at','invited_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'file_managements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type','id','user_id','school_location_id','file_management_status_id','handledby','notes','name','origname','typedetails','parent_id','archived'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::created(function (FileManagement $fileManagement) {
            FileManagementStatusLog::create([
                'file_management_id' => $fileManagement->getKey(),
                'file_management_status_id' => $fileManagement->file_management_status_id
            ]);
        });

        static::updated(function (FileManagement $fileManagement) {

            // logging statuses if changed
            if ($fileManagement->getOriginal('file_management_status_id') != $fileManagement->file_management_status_id) {
                FileManagementStatusLog::create([
                    'file_management_id' => $fileManagement->getKey(),
                    'file_management_status_id' => $fileManagement->file_management_status_id
                ]);
            }
        });

    }

    public function getTypedetailsAttribute($value)
    {
        try {
            return json_decode($value);
        }
        catch(\Exception $e){
            return (object) [];
        }
    }

    public function setTypedetailsAttribute($value)
    {
        $this->attributes['typedetails'] = json_encode($value);
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
