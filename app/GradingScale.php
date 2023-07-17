<?php namespace tcCore;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class GradingScale extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'grading_scales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'system_name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolLocations() {
        return $this->hasMany('tcCore\SchoolLocation');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'name':
                    $query->where('name', 'LIKE', '%'.$value.'%');
                    break;
                case 'system_name':
                    $query->where('system_name', 'LIKE', '%'.$value.'%');
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'system_name':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }

            switch (strtolower($key)) {
                case 'id':
                case 'name':
                case 'system_name':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }


}
