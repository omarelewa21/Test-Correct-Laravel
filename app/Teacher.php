<?php namespace tcCore;

use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Teacher extends BaseModel {

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
    protected $table = 'teachers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'class_id', 'subject_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::created(function(Teacher $teacher) {
            Queue::push(new UpdatePValueUsers($teacher->getAttribute('class_id'), $teacher->getAttribute('subject_id'), $teacher->getAttribute('user_id'), null, null, null));
        });

        static::updated(function(Teacher $teacher) {
            if ($teacher->getAttribute('class_id') != $teacher->getOriginal('class_id') || $teacher->getAttribute('subject_id') != $teacher->getOriginal('subject_id') || $teacher->getAttribute('user_id') != $teacher->getOriginal('user_id')) {
                Queue::push(new UpdatePValueUsers($teacher->getAttribute('class_id'), $teacher->getAttribute('subject_id'), $teacher->getAttribute('user_id'), $teacher->getOriginal('class_id'), $teacher->getOriginal('subject_id'), $teacher->getOriginal('user_id')));
            }
        });

        static::deleted(function(Teacher $teacher) {
            Queue::push(new UpdatePValueUsers(null, null, null, $teacher->getOriginal('class_id'), $teacher->getOriginal('subject_id'), $teacher->getOriginal('user_id')));
        });
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function schoolClass() {
        return $this->belongsTo('tcCore\SchoolClass', 'class_id');
    }

    public function subject() {
        return $this->belongsTo('tcCore\Subject');
    }

    public function tests() {
        return $this->hasMany(Test::class,'author_id','user_id');
    }

    public function scopeFiltered($query, $filters = [], $sorting = []) {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'id':
                    if (is_array($value)) {
                        $query->whereIn('id', $value);
                    } else {
                        $query->where('id', '=', $value);
                    }
                    break;
                case 'user_id':
                    if (is_array($value)) {
                        $query->whereIn('user_id', $value);
                    } else {
                        $query->where('user_id', '=', $value);
                    }
                    break;
                case 'class_id':
                    if (is_array($value)) {
                        $query->whereIn('class_id', $value);
                    } else {
                        $query->where('class_id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    if (is_array($value)) {
                        $query->whereIn('subject_id', $value);
                    } else {
                        $query->where('subject_id', '=', $value);
                    }
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
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
                    $query->orderBy($key, $value);
                    break;
            }
        }
    }

    public function importLog()
    {
        return $this->hasOne(TeacherImportLog::class, 'teacher_id', 'id');
    }
}
