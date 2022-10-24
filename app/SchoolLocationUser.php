<?php

namespace tcCore;

use tcCore\Lib\Models\BaseModel;

class SchoolLocationUser extends BaseModel
{
    protected $dates = ['created_at', 'deleted_at'];

    protected $fillable = ['school_location_id', 'user_id', 'external_id'];

    protected $table = 'school_location_user';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schoolLocation()
    {
        return $this->belongsTo(SchoolLocation::class);
    }
}