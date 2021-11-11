<?php

namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Lib\User\Roles;
use tcCore\Traits\UuidTrait;

class Info extends Model
{
    use SoftDeletes;
    use UuidTrait;

    public const ACTIVE = 'ACTIVE';
    public const INACTIVE = 'INACTIVE';

    protected $casts = [
        'uuid' => EfficientUuid::class,
//        'show_from' => 'datetime:Y-m-d H:i',
//        'show_until' => 'datetime:Y-md H:i',
        'for_all' => 'boolean',
    ];

    protected $fillable = [
      'title_nl',
        'title_en',
        'content_nl',
        'content_en',
        'show_from',
        'show_until',
        'status',
        'for_all',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function (Info $info) {
            $info->for_all = !!$info->for_all;
            if ($info->status !== self::ACTIVE){
                $info->status = self::INACTIVE;
            }
            if($info->roleIds){
                $info->roles()->sync($info->roleIds);
            }
            return $info;
        });

        static::creating(function(Info $info){
            $info->created_by = Auth::id();
        });

        static::deleting(function(Info $info){

        });
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public static function getInfoForUser(User $user)
    {
        $roleIds = $user->roles->map(function(Role $role){
           return $role->getKey();
        })->toArray();
        $infoIdsFromRoles = DB::table('info_roles')->whereIn('role_id',$roleIds)->pluck('info_id')->toArray();
        return Info::where('status',self::ACTIVE)
                    ->where('show_from','>=', Carbon::now())
                    ->where('show_until','<=',Carbon::now())
                    ->where(function($query) use ($infoIdsFromRoles){
                       $query->where('for_all',true)
                           ->orWhereIn('id',$infoIdsFromRoles);
                    })
                    ->orderBy('show_from','asc')
                    ->get();
    }

}
