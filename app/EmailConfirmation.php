<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use tcCore\Traits\UuidTrait;

class EmailConfirmation extends Model
{

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'email_confirmations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'uuid'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

}
