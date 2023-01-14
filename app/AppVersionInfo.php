<?php

namespace tcCore;

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
        'platform',
        'platform_version',
        'platform_version_major',
        'platform_version_minor',
        'platform_version_patch',
        'platform_type',
        'browser_type',
        'browser_version_major',
        'browser_version_minor',
        'browser_version_patch',
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
            'version'                => session()->get('TLCVersion'),
            'platform'               => session()->get('TLCPlatform'),
            'platform_version'       => session()->get('TLCPlatformVersion'),
            'platform_version_major' => session()->get('TLCPlatformVersionMajor', null),
            'platform_version_minor' => session()->get('TLCPlatformVersionMinor', null),
            'platform_version_patch' => session()->get('TLCPlatformVersionPatch', null),
            'platform_type'          => session()->get('TLCPlatformType'),
            'browser_type'           => session()->get('TLCBrowserType', null),
            'browser_version_major'  => session()->get('TLCBrowserVersionMajor', null),
            'browser_version_minor'  => session()->get('TLCBrowserVersionMinor', null),
            'browser_version_patch'  => session()->get('TLCBrowserVersionPatch', null),
            'headers'                => json_encode(session()->get('headers')),
            'version_check_result'   => session()->get('TLCVersioncheckResult'),
        ]);
    }
}
