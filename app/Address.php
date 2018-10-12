<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends BaseModel {

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
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['address', 'postal', 'city', 'country'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'address':
                    $query->where('address', 'LIKE', '%'.$value.'%');
                    break;
                case 'postal':
                    $query->where('postal', 'LIKE', '%'.$value.'%');
                    break;
                case 'city':
                    $query->where('city', 'LIKE', '%'.$value.'%');
                    break;
                case 'country':
                    $query->where('country', 'LIKE', '%'.$value.'%');
                    break;
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'address':
                case 'postal':
                case 'city':
                case 'country':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch(strtolower($key)) {
                case 'id':
                case 'address':
                case 'postal':
                case 'city':
                case 'country':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }
}
