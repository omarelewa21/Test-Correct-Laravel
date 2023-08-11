<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class TestTakeCode extends Model
{
    use SoftDeletes, UuidTrait;

    const PREFIX = 'AA';

    protected $casts = [
        'uuid'                      => EfficientUuid::class,
        'deleted_at'                => 'datetime',
        'rating_visible_expiration' => 'datetime',
    ];

    protected $fillable = ['test_take_id', 'rating_visible_expiration'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (TestTakeCode $testCode) {
            $testCode->code = self::generateUniqueCode();
            $testCode->prefix = self::PREFIX;
        });
    }

    private static function generateUniqueCode()
    {
        do {
            $code = self::createRandomCode();
        } while (self::whereCode($code)->exists());

        return $code;
    }

    private static function createRandomCode(): int
    {
        return random_int(100000, 999999);
    }

    public function testTake()
    {
        return $this->belongsTo(TestTake::class);
    }

    public function getSchoolLocationFromTestTakeCode()
    {
        return User::join('test_takes', 'test_takes.user_id', '=', 'users.id')
            ->where('test_takes.id', $this->test_take_id)
            ->value('users.school_location_id');
    }

    protected function displayCode(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $codes = str_split($attributes['code'], 3);
                return sprintf("%s %s %s", $attributes['prefix'], $codes[0], $codes[1]);
            }
        );
    }
}
