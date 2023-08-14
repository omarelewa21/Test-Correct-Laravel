<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestTakeStatus extends BaseModel
{

    use SoftDeletes;

    const STATUS_PLANNED = 1;
    const STATUS_TEST_NOT_TAKEN = 2;
    const STATUS_TAKING_TEST = 3;
    const STATUS_HANDED_IN = 4;
    const STATUS_TAKEN_AWAY = 5;
    const STATUS_TAKEN = 6;
    const STATUS_DISCUSSING = 7;
    const STATUS_DISCUSSED = 8;
    const STATUS_RATED = 9;

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
    protected $table = 'test_take_statuses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'is_individual_status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function testTakes()
    {
        return $this->hasMany('tcCore\TestTake');
    }

    public function testParticipants()
    {
        return $this->hasMany('tcCore\TestParticipant');
    }

    public static function testTakenStatusses()
    {
        return collect([
            self::STATUS_HANDED_IN,
            self::STATUS_TAKEN_AWAY,
            self::STATUS_TAKEN,
            self::STATUS_DISCUSSING,
            self::STATUS_DISCUSSED
        ]);
    }
}
