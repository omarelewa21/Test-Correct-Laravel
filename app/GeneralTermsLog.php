<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeneralTermsLog extends Model
{
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = ['deleted_at' => 'datetime',];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'accepted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
