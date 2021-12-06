<?php namespace tcCore;

use Illuminate\Http\Request;
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
            ->with(['user', 'handler', 'status', 'status.parent'])
            ->select('file_managements.*');

       if ($user->hasRole('Teacher')) {
            $query->where(function ($query) use ($user) {
                $query->where('file_managements.user_id', $user->getKey())
                    ->orWhere('file_managements.handledby', $user->getKey());
            });
            if ($user->isToetsenbakker()) {
                $query->where('file_managements.archived', false);
            } else {
                $query->where('file_managements.school_location_id', $user->school_location_id);
            }
        } else if ($user->hasRole('Account manager')) {
            $query->whereIn('file_managements.school_location_id', (new SchoolHelper())->getRelatedSchoolLocationIds($user));
        }

        $query->join('school_locations','file_managements.school_location_id','=','school_locations.id')->with('schoolLocation');


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
            } else {
                $this->handleFilterDefault($query,$key,$val);
            }
        }
    }

    protected function handleFilterEducationLevelYears($query, $val = [])
    {
        $query->whereIn('file_managements.education_level_year',array_map('intval',$val));
    }

    protected function handleFilterEducationLevels($query, $val = [])
    {
        $query->whereIn('file_managements.education_level_id',array_map('intval',$val));
    }

    protected function handleFilterNotes($query,$val)
    {
        $query->where('file_managements.notes','like','%'.$val.'%');
    }


    protected function handleFilterClass($query,$val)
    {
        $query->where('file_managements.class','like','%'.$val.'%');
    }

    protected function handleFilterSubject($query,$val)
    {
        $query->where('file_managements.subject','like','%'.$val.'%');
    }

    protected function handleFilterHandlerid($query,$val = [])
    {
        $query->whereIn('file_managements.handledby',array_map('intval',$val));
    }

    protected function handleFilterTeacherid($query,$val = [])
    {
        $query->whereIn('file_managements.user_id',array_map('intval',$val));
    }

    protected function handleFilterSchoolLocation($query,$val = [])
    {
        $query->whereIn('file_managements.school_location_id',array_map('intval',$val));
    }

    protected function handleFilterTestName($query, $val)
    {
        $query->where('file_managements.test_name','like','%'.$val.'%');
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

    protected function handleSortingClass($query,$dir)
    {
        $query->orderBy('file_managements.class',$dir);
    }

    protected function handleSortingName($query,$dir)
    {
        $query->orderBy('file_managements.test_name',$dir);
    }

    protected function handleSortingSubject($query,$dir)
    {
        $query->orderBy('file_managements.subject',$dir);
    }

    protected function handleSortingHandledby($query,$dir)
    {
        $query->join('users as handlers','file_managements.handledby','=','handlers.id')
            ->orderBy('handlers.name',$dir);
    }

    protected function handleSortingTeacher($query,$dir)
    {
        $query->join('users','file_managements.user_id','=','users.id')
                ->orderBy('users.name',$dir);
    }

    protected function handleSortingSchoolLocationCode($query, $dir)
    {
        $query->orderBy('school_locations.external_main_code',$dir)
            ->orderBy('school_locations.external_sub_code',$dir);
    }

    protected function handleSortingSchoolLocation($query,$dir)
    {
        $query->orderBy('school_locations.name',$dir);
    }

    protected function handleSortingCreatedAt($query,$dir)
    {
        $query->orderBy('file_managements.created_at', $dir);
    }

    public static function getBuilderForUsers(User $user, $type = 'testupload')
    {
        $ids = FileManagement::where('type',$type)
                ->whereIn('file_managements.school_location_id',(new SchoolHelper())->getRelatedSchoolLocationIds($user))
                ->select('user_id','handledby')
                ->get()
                ->map(function(FileManagement $fm){
                    return [$fm->user_id,$fm->handledby];
                })
                ->collapse();


        return User::withTrashed()
                ->whereIn('id', $ids)
                ->select('id','name_first','name_suffix','name')
                ->groupBy('users.id')
                ->orderBy('name','asc');
    }

    public static function getBuilderForEducationLevels(User $user, $type = 'testupload')
    {
        $ids = FileManagement::where('type',$type)
            ->whereIn('file_managements.school_location_id',(new SchoolHelper())->getRelatedSchoolLocationIds($user))
            ->select('education_level_id')
            ->get()
            ->unique();


        return EducationLevel::withTrashed()
            ->whereIn('id', $ids)
            ->orderBy('max_years','asc')
            ->orderBy('name','asc');
    }
}
