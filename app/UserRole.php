<?php namespace tcCore;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolLocationActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\Jobs\CountSchoolLocationStudents;
use tcCore\Jobs\CountSchoolLocationTeachers;
use tcCore\Jobs\CountSchoolLocationTests;
use tcCore\Jobs\CountSchoolLocationTestsTaken;
use tcCore\Jobs\CountSchoolQuestions;
use tcCore\Jobs\CountSchoolStudents;
use tcCore\Jobs\CountSchoolTeachers;
use tcCore\Jobs\CountSchoolTests;
use tcCore\Jobs\CountSchoolTestsTaken;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends BaseModel {

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
    protected $table = 'user_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'role_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    public static function boot()
    {
        parent::boot();

        static::saving(function(UserRole $userRole)
        {
            $user = $userRole->user;
            $role = $userRole->role;

            $schoolLocationId = $user->getAttribute('school_location_id');
            $schoolId = $user->getAttribute('school_id');

            if (empty($schoolLocationId) && !empty($schoolId)) {
                if ($role->getAttribute('name') === 'Student') {
                    Queue::push(new CountSchoolStudents($user->school));
                } elseif ($role->getAttribute('name') === 'Teacher') {
                    Queue::push(new CountSchoolTeachers($user->school));
                    Queue::push(new CountSchoolActiveTeachers($user->school));
                    Queue::push(new CountSchoolQuestions($user->school));
                    Queue::push(new CountSchoolTests($user->school));
                    Queue::push(new CountSchoolTestsTaken($user->school));
                }
            } elseif(!empty($schoolLocationId) && !empty($schoolId)) {
                if ($role->getAttribute('name') === 'Student') {
                    Queue::push(new CountSchoolLocationStudents($user->school));
                } elseif ($role->getAttribute('name') === 'Teacher') {
                    Queue::push(new CountSchoolLocationTeachers($user->schoolLocation));
                    Queue::push(new CountSchoolLocationActiveTeachers($user->schoolLocation));
                    Queue::push(new CountSchoolLocationQuestions($user->schoolLocation));
                    Queue::push(new CountSchoolLocationTests($user->schoolLocation));
                    Queue::push(new CountSchoolLocationTestsTaken($user->schoolLocation));
                }
            }
        });

        static::deleted(function(UserRole $userRole)
        {
            $user = $userRole->user;
            $role = $userRole->role;
            $schoolLocationId = $user->getAttribute('school_location_id');
            $schoolId = $user->getAttribute('school_id');

            if (empty($schoolLocationId) && !empty($schoolId)) {
                if ($role->getAttribute('name') === 'Student') {
                    Queue::push(new CountSchoolStudents($user->school));
                } elseif ($role->getAttribute('name') === 'Teacher') {
                    Queue::push(new CountSchoolTeachers($user->school));
                    Queue::push(new CountSchoolActiveTeachers($user->school));
                    Queue::push(new CountSchoolQuestions($user->school));
                    Queue::push(new CountSchoolTests($user->school));
                    Queue::push(new CountSchoolTestsTaken($user->school));
                }
            } elseif(!empty($schoolLocationId) && !empty($schoolId)) {
                if ($role->getAttribute('name') === 'Student') {
                    Queue::push(new CountSchoolLocationStudents($user->schoolLocation));
                } elseif ($role->getAttribute('name') === 'Teacher') {
                    Queue::push(new CountSchoolLocationTeachers($user->schoolLocation));
                    Queue::push(new CountSchoolLocationActiveTeachers($user->schoolLocation));
                    Queue::push(new CountSchoolLocationQuestions($user->schoolLocation));
                    Queue::push(new CountSchoolLocationTests($user->schoolLocation));
                    Queue::push(new CountSchoolLocationTestsTaken($user->schoolLocation));
                }
            }
        });
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function role() {
        return $this->belongsTo('tcCore\Role');
    }
}
