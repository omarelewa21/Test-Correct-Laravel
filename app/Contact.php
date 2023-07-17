<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Contact extends BaseModel {

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
    protected $table = 'contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'address', 'postal', 'city', 'country', 'phone', 'mobile', 'email', 'note'];

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
                case 'name':
                    $query->where('name', 'LIKE', '%'.$value.'%');
                    break;
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
                case 'phone':
                    $query->where('phone', 'LIKE', '%'.$value.'%');
                    break;
                case 'mobile':
                    $query->where('mobile', 'LIKE', '%'.$value.'%');
                    break;
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'name':
                case 'address':
                case 'postal':
                case 'city':
                case 'country':
                case 'phone':
                case 'mobile':
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
                case 'name':
                case 'address':
                case 'postal':
                case 'city':
                case 'country':
                case 'phone':
                case 'mobile':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }


}
