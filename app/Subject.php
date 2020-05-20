<?php namespace tcCore;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;

class Subject extends BaseModel implements AccessCheckable {

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'abbreviation', 'section_id', 'base_subject_id','demo'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $schoolLocations;

    public function baseSubject() {
        return $this->belongsTo('tcCore\BaseSubject');
    }

    public function section() {
        return $this->belongsTo('tcCore\Section');
    }

    public function teachers() {
        return $this->hasMany('tcCore\Teacher');
    }

    public function questions() {
        return $this->hasMany('tcCore\Question');
    }

    public function pValue() {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings() {
        return $this->hasMany('tcCore\Rating');
    }

    public function averageRatings() {
        return $this->hasMany('tcCore\AverageRating');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles)) {
            $query->whereIn('section_id', function ($query) {
                $query->select('id')
                    ->from(with(new Section())->getTable())
                    ->whereNull('deleted_at');
                with(new Section())->scopeFiltered($query);
            });
        }

        foreach($filters as $key => $value) {
            switch($key) {
                case 'user_id':
                    $query->whereIn('id', function ($query) use ($value) {
                        $query->select('subject_id')
                            ->from(with(new Teacher())->getTable())
                            ->whereNull('deleted_at');
                        if (is_array($value)) {
                            $query->whereIn('user_id', $value);
                        } else {
                            $query->where('user_id', '=', $value);
                        }
                    });
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'abbreviation':
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
                case 'abbreviation':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function canAccess()
    {
        $roles = Roles::getUserRoles();
        if (in_array('Administrator', $roles)) {
            return true;
        }

        return Section::filtered(['id' => $this->getAttribute('section_id')])->count() > 0;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to subject denied');
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function (self $item) {
            if ($item->getOriginal('demo') == true) return false;
        });

        static::deleting(function (self $item) {
            if ($item->getOriginal('demo') == true) return false;
        });
    }
}
