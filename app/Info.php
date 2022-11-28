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

    protected $appends = ['title','content'];

    public function getTitleAttribute()
    {
        if(Auth::user()){
            return $this->getLanguagePart('title',Auth::user()->getActiveLanguage());
        }
        return '';
    }

    public function getContentAttribute()
    {
        if(Auth::user()){
            return $this->getLanguagePart('content',Auth::user()->getActiveLanguage());
        }
        return '';
    }

    protected function getLanguagePart($part,$language)
    {
        return $this->attributes[sprintf('%s_%s',$part,$language)] ?? '';
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function(Info $info){
            $info->created_by = Auth::id();
        });

        static::deleting(function(Info $info){

        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function saveRoleInfo($data)
    {
        $data = (object) $data;

        if(property_exists($data,'roles') && $data->roles){
            $this->roles()->sync($data->roles);
        }
        return $this;
    }

    public static function getInfoForUser(User $user, $discardInfosRemovedByUser=false)
    {
        $roleIds = $user->roles->map(function(Role $role){
           return $role->getKey();
        })->toArray();
        $infoIdsFromRoles = DB::table('info_role')->whereIn('role_id',$roleIds)->pluck('info_id')->toArray();
        $infos = new Info;
        if($discardInfosRemovedByUser){
            $infos = Info::doesntHave('infoRemovedByUser');
        }
        return $infos->where('status',self::ACTIVE)
                    ->where('show_from','<=', Carbon::now())
                    ->where('show_until','>=',Carbon::now())
                    ->where(function($query) use ($infoIdsFromRoles){
                       $query->where('for_all',true)
                           ->orWhereIn('id',$infoIdsFromRoles);
                    })
                    ->orderBy('show_from','asc')
                    ->get();
    }

    public function isVisibleForUser(User $user)
    {
        return self::getInfoForUser($user)->contains($this);
    }

    public function infoRemovedByUser(){
        return $this->hasMany(UserInfosDontShow::class)->where('user_id', auth()->id());
    }

}
