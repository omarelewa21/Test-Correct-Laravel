<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class TestTakeCode extends Model
{
    use SoftDeletes, UuidTrait;

    const PREFIX = 'AA';

    protected $casts = [
        'uuid' => EfficientUuid::class
    ];

    protected $dates = ['deleted_at', 'rating_visible_expiration'];

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
}
