<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Scopes\AttainmentScope;
use tcCore\Traits\UuidTrait;

class Attainment extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
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
    protected $table = 'attainments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['base_subject_id', 'education_level_id', 'attainment_id', 'code', 'subcode', 'subsubcode', 'description', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new AttainmentScope);
    }

    public static function bootWithoutGlobalScope()
    {
        parent::boot();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function baseSubject() {
        return $this->belongsTo('tcCore\BaseSubject');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function educationLevel() {
        return $this->belongsTo('tcCore\EducationLevel');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attainment() {
        return $this->belongsTo('tcCore\Attainment');
    }

    public function questionAttainments() {
        return $this->hasMany('tcCore\QuestionAttainment', 'attainment_id');
    }

    public function questions() {
        return $this->belongsToMany('tcCore\Question', 'question_attainments')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'status':
                    if (is_array($value)) {
                        $query->whereIn('status', $value);
                    } else {
                        $query->where('status', '=', $value);
                    }
                    break;
                case 'education_level_id':
                    if (is_array($value)) {
                        $query->whereIn('education_level_id', $value);
                    } else {
                        $query->where('education_level_id', '=', $value);
                    }
                    break;
                case 'attainment_id':
                    if (is_array($value)) {
                        $query->whereIn('attainment_id', $value);
                    } else {
                        $query->where('attainment_id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    $query->whereIn('base_subject_id', function ($query) use ($value) {
                        $query->select('base_subject_id')
                            ->from(with(new Subject())->getTable());
                        if (is_array($value)) {
                            $query->whereIn('id', $value);
                        } else {
                            $query->where('id', '=', $value);
                        }
                        $query->where('deleted_at', null);
                    });
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'code':
                case 'subcode':
                case 'description':
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
                case 'code':
                case 'subcode':
                case 'description':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
