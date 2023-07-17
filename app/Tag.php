<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Tag extends BaseModel {

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
    protected $table = 'tags';

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

    public function tagRelations() {
        return $this->hasMany('tcCore\TagRelation');
    }

    public function questions()
    {
        return $this->morphedByMany('tcCore\Question', 'tag_relation')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function test()
    {
        return $this->morphedByMany('tcCore\Test', 'tag_relation')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public static function findOrCreateByName($names)
    {
        if (is_array($names)) {
            $names = array_unique($names);
            $tags = static::whereIn('name', $names);
        } else {
            $tags = static::where('name', $names);
            $names = array($names);
        }

        $tags = $tags->pluck('name', 'id')->all();
        $tags = array_map('strtolower', $tags);

        foreach($names as $name) {
            if(!in_array(strtolower($name), $tags)) {
                $tag = static::create(['name' => strtolower($name)]);
                $tags[$tag->getKey()] = $tag->getAttribute('name');
            }
        }

        return array_keys($tags);
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'name':
                    $query->where('name', 'LIKE', '%'.$value.'%');
                    break;
                case 'complete_name':
                    $query->where('name', 'LIKE', $value.'%');
                    break;
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
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
            switch(strtolower($key)) {
                case 'id':
                case 'name':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }


}
