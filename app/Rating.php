<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\Rating\CalculateRatingForUser;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends BaseModel {

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
    protected $table = 'ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_participant_id', 'user_id', 'period_id', 'subject_id', 'school_class_id', 'education_level_id', 'education_level_year', 'rating', 'score', 'max_score', 'weight'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function(Rating $rating)
        {
            Queue::push(new CalculateRatingForUser($rating->user));
        });
    }

    public function testParticipant() {
        return $this->belongsTo('tcCore\TestParticipant');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function period() {
        return $this->belongsTo('tcCore\Period');
    }

    public function subject() {
        return $this->belongsTo('tcCore\Subject');
    }

    public function schoolClass() {
        return $this->belongsTo('tcCore\SchoolClass')->withTrashed();
    }

    public function educationLevel() {
        return $this->belongsTo('tcCore\EducationLevel');
    }
}
