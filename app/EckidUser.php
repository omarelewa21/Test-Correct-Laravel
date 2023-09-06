<?php namespace tcCore;


use tcCore\Lib\Models\BaseModel;


class EckidUser extends BaseModel {



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
    protected $table = 'eckid_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'eckid'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];




    public function user() {
        return $this->belongsTo('tcCore\User');
    }

}
