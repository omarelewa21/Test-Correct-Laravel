<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use tcCore\Lib\Models\BaseModel;
use tcCore\Traits\UuidTrait;

class Shortcode extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

    const SHORTCODE_PREFIX = '';
    const SHORTCODE_LENGTH = 6;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $appends = ['link'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','created_at','updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLinkAttribute()
    {
        return sprintf('%s%s',config('custom.shortcode.link'),$this->code);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (Shortcode $shortcode) {
            $codeExists = true;
            while ($codeExists === true){
                $code = sprintf('%s%s', static::SHORTCODE_PREFIX, Str::random(static::SHORTCODE_LENGTH));
                if (!Shortcode::where('code', $code)->exists()) {
                    $codeExists = false;
                }
            }
            $shortcode->code = $code;
        });
    }

    public function getRouteKeyName()
    {
        return 'code';
    }

}
