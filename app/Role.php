<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends BaseModel
{

    use SoftDeletes;

    const TEACHER = 1;
    const INVIGILATOR = 2;
    const STUDENT = 3;
    const ADMINISTRATOR = 4;
    const ACCOUNTMANAGER = 5;
    const SCHOOLMANAGER = 6;
    const SCHOOLMANAGEMENT = 7;
    const MENTOR = 8;
    const PARENT = 9;
    const TECHADMINISTRATOR = 10;
    const SUPPORT = 11;
    const TESTTEAM = 12;

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
    protected $table = 'roles';

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

    public function users()
    {
        $this->belongsToMany('tcCore\User', 'user_roles')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()]);
    }
}
