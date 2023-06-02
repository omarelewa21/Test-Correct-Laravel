<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class BaseSubject extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'     => EfficientUuid::class,
        'wsc_lang' => WscLanguage::class,
    ];

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
    protected $table = 'base_subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function subjects() {
        return $this->hasMany('tcCore\Subject');
    }

    public function attainments() {
        return $this->hasMany('tcCore\Attainment');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'user_id':
                    $query->from(with(new Subject())->getTable())
                        ->where('deleted_at', null)
                        ->whereIn('id', function ($query) use ($value) {
                        $query->whereIn('base_subject_id', function ($query) use ($value) {
                            $query->select('subject_id')
                                ->from(with(new Teacher())->getTable())
                                ->where('deleted_at', null);
                            if (is_array($value)) {
                                $query->whereIn('user_id', $value);
                            } else {
                                $query->where('user_id', '=', $value);
                            }
                        });
                    });
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
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
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function scopeForLevel($query, $level = null)
    {
        if ($level) {
            return $query->where('level', 'like', '%' . $level . '%');
        }
        return $query;
    }

    public function scopeNationalItemBankFiltered($query) //todo unused?
    {
        return $query->whereIn('id',
                Subject::nationalItemBankFiltered([], ['name' => 'asc'])
                ->distinct()
                ->pluck('base_subject_id')
        );
    }


    public static function scopeCurrentForAuthUser($query)
    {
        return $query->whereIn('id', Subject::filtered(['user_current' => Auth::id()])->select('base_subject_id'));
    }

    // shouldn't this method be used for the scopeCurrentForAuthUser ???
    // 20220913 By Erik maybe not as the scopeCurrentForAuthUser is also checking the current school period so that one might even be the better one, more restrictive
    public static function getIdsForUserInCurrentSchoolLocation(User $user) : array
    {
        return $user->subjectsInCurrentLocation()->pluck('base_subject_id')->unique()->toArray();
    }

}
