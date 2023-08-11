<?php

namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use tcCore\Lib\Models\BaseModel;
use tcCore\Traits\UuidTrait;

class Shortcode extends BaseModel
{

    use SoftDeletes;

    const SHORTCODE_PREFIX = '';
    const SHORTCODE_LENGTH = 6;
    const MAX_VALID_IN_SECONDS = 5;

    protected $appends = ['link'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = [
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id'];

    public static function createForUser(User $user)
    {
        self::where('user_id', $user->getKey())->forceDelete();

        return self::create(['user_id' => $user->getKey()]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLinkAttribute()
    {
        return sprintf('%s%s', config('shortcode.link'), $this->code);
    }

    public static function isValid($code)
    {
        $result = false;
        $shortcode = self::whereCode($code)->first();

        if ($shortcode && Carbon::now()->diffInSeconds($shortcode->created_at) < Shortcode::MAX_VALID_IN_SECONDS) {
            $result = $shortcode->user_id;
        }

        return $result;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function (Shortcode $shortcode) {

            $codeExists = true;
            while ($codeExists === true) {
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
