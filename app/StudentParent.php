<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class StudentParent extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;

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
    protected $table = 'student_parents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'user_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['parent_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function parent() {
        return $this->belongsTo('tcCore\User', 'parent_id');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }
}
