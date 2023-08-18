<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class AverageRating extends BaseModel {

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = ['deleted_at' => 'datetime',];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'average_ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'school_year_id', 'school_class_id', 'subject_id', 'average'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function schoolYear() {
        return $this->belongsTo('tcCore\SchoolYear');
    }

    public function schoolClass() {
        return $this->belongsTo('tcCore\SchoolClass')->withTrashed();
    }

    public function subject() {
        return $this->belongsTo('tcCore\Subject');
    }
}
