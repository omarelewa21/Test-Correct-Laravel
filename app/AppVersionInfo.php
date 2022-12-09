<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVersionInfo extends BaseModel
{

    public $incrementing = false;
    protected $keyType = 'string';

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_version_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'version',
        'os',
        'user_os',
        'user_os_version',
        'headers',
        'version_check_result'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();
        static::creating(function (AppVersionInfo $appVersionInfo) {
            $appVersionInfo->id = Str::uuid();
            $appVersionInfo->user_id = Auth::id();
        });
    }

    public function user()
    {
        return $this->belongsTo('tcCore\User');
    }

    public static function createFromSession()
    {
        self::create([
            'version'              => session()->get('TLCVersion'),
            'os'                   => session()->get('TLCOs'),
            'user_os'              => session()->get('UserOsPlatform'),
            'user_os_version'      => session()->get('UserOsVersion'),
            'headers'              => json_encode(session()->get('headers')),
            'version_check_result' => session()->get('TLCVersioncheckResult'),
        ]);
    }
}
