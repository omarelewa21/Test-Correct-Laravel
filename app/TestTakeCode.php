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

    protected $dates = ['deleted_at'];

    protected $fillable = ['test_take_id'];

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

    public function  testTake()
    {
        return $this->belongsTo(TestTake::class);
    }
}
