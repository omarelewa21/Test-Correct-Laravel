<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use tcCore\Traits\UuidTrait;

class TemporaryLogin extends Model
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
    protected $table = 'temporary_login';

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

    public function createCakeUrl() {
        return config('app.url_login').'users/temporary_login/'.$this->uuid;
    }

}
