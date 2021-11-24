<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use tcCore\Http\Helpers\SchoolHelper;
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
    protected $fillable = ['type','id','user_id','school_location_id','file_management_status_id','handledby','notes','name','origname','typedetails','parent_id','archived','form_id',
                            'class','subject','education_level_year','education_level_id','test_name','test_kind_id','orig_filenames'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

//        static::created(function (FileManagement $fileManagement) {
//            $fileManagement->refresh());
//            FileManagementStatusLog::create([
//                'file_management_id' => $fileManagement->getKey(),
//                'file_management_status_id' => $fileManagement->file_management_status_id
//            ]);
//        });
//
//        static::updated(function (FileManagement $fileManagement) {
//
//            // logging statuses if changed
//            if ($fileManagement->getOriginal('file_management_status_id') != $fileManagement->file_management_status_id) {
//                FileManagementStatusLog::create([
//                    'file_management_id' => $fileManagement->getKey(),
//                    'file_management_status_id' => $fileManagement->file_management_status_id
//                ]);
//            }
//        });

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

    public function scopeFiltered($query, $user, $filters = [], $sorting = [])
    {
        $query->whereNull('parent_id')
            ->with(['user', 'handler', 'status', 'status.parent']);

       if ($user->hasRole('Teacher')) {
            $query->where(function ($query) use ($user) {
                $query->where('user_id', $user->getKey())
                    ->orWhere('handledby', $user->getKey());
            });
            if ($user->isToetsenbakker()) {
                $query->where('archived', false);
            } else {
                $query->where('school_location_id', $user->school_location_id);
            }
        } else if ($user->hasRole('Account manager')) {
            $query->whereIn('school_location_id', (new SchoolHelper())->getRelatedSchoolLocationIds($user))
                ->with(['schoolLocation']);
        }

        $this->handleFilters($query,$filters);

        $this->handleSorting($query,$user, $sorting);

        return $query;
    }

    protected function handleFilters($query,$filters = [])
    {
        foreach($filters as $key => $val){
            $methodName = sprintf('handleFilter%s',ucfirst(strtolower($key)));
            if(method_exists($this,$methodName)){
                $this->$methodName($query,$val);
            }
        }
    }

    protected function handleFilterType($query,$value)
    {
        $this->handleFilterDefault($query,'type',$value);
    }

    protected function handleFilterDefault($query,$key,$value)
    {
        $query->where($key,$value);
    }

    protected function handleSorting($query, User $user, $sorting = [])
    {
        $sortingFound = false;
        foreach($sorting as $key => $val){
            if(!in_array($val,['asc','desc'])){
                $val = 'asc';
            }
            $methodName = sprintf('handleSorting%s',ucfirst(strtolower($key)));
            if(method_exists($this,$methodName)){
                $this->$methodName($query,$val);
                $sortingFound = true;
            }
        }

        if($user->hasRole('Account manager')){
            // we want to order by filemanagementstatus displayorder, but as it has the same fieldnames as file_managements table
            // we can't use a join. Therefor we first get all the statusIds in the correct order and then order by them
            $statusIds = FileManagementStatus::orderBy('displayorder')->pluck('id')->toArray();
            $query->orderByRaw('FIELD(file_management_status_id,' . implode(',', $statusIds) . ')', 'asc');
        }

        if(!$sortingFound){
            $query->orderBy('file_managements.created_at', 'asc');
        }
    }



    protected function handleSortingName($query,$dir)
    {
        $query->orderBy('file_managements.test_name',$dir);
    }

    protected function handleSortingSubject($query,$dir)
    {
        $query->orderBy('file_managements.subject',$dir);
    }

    protected function handleSortingTeacher($query,$dir)
    {
        $query->orderBy('users.name',$dir);
    }

    protected function handleSortingSchoolLocation($query,$dir)
    {
        $query->orderBy('school_locations.name',$dir);
    }

    protected function handleSortingCreatedAt($query,$dir)
    {
        $query->orderBy('file_managements.created_at', $dir);
    }
}
