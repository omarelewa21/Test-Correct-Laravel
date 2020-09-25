<?php namespace tcCore;

use Illuminate\Support\Facades\Log;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class TestQuestion extends BaseModel {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_id', 'question_id', 'order', 'maintain_position', 'discuss'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $callbacks = true;

    protected $recount = true;

    public static function boot()
    {
        parent::boot();

        static::saving(function(TestQuestion $testQuestion) {
            $testQuestion->load('test');
            return $testQuestion->test->allowChange();
        });

        static::saved(function(TestQuestion $testQuestion) {
            if ($testQuestion->doCallbacks() && ($testQuestion->getOriginal('order') != $testQuestion->getAttribute('order') || $testQuestion->getOriginal('test_id') != $testQuestion->getAttribute('test_id'))) {
                $testQuestion->test->reorder($testQuestion);
            }

            if ($testQuestion->doCallbacks() && $testQuestion->getOriginal('question_id') != $testQuestion->getAttribute('question_id') && $testQuestion->question instanceof GroupQuestion) {
                $testQuestion->test->performMetadata();
            }

            $testQuestion->test->processChange();

        });

        $metadataCallback = function(TestQuestion $testQuestion) {
            if ($testQuestion->doCallbacks()) {
                $testQuestion->test->performMetadata();
            }
        };

        static::created($metadataCallback);

        static::restored($metadataCallback);

        static::deleted($metadataCallback);
    }

    public function setCallbacks($callbacks) {
        $this->callbacks = ($callbacks === true);
    }

    public function doCallbacks() {
        return $this->callbacks;
    }

    public function test() {
        return $this->belongsTo('tcCore\Test', 'test_id');
    }

    public function question() {
        return $this->belongsTo('tcCore\Question', 'question_id');
    }


    public function duplicate($parent, array $attributes = [], $reorder = true) {
        $testQuestion = $this->replicate();
        $testQuestion->fill($attributes);

        $testQuestion->setAttribute('uuid', Uuid::uuid4());

        if ($reorder === false) {
            $testQuestion->setCallbacks(false);
        }

        if ($parent->testQuestions()->save($testQuestion) === false) {
            return false;
        }

        if ($reorder === false) {
            $testQuestion->setCallbacks(true);
        }

        return $testQuestion;
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'test_id':
                    if (is_array($value)) {
                        $query->whereIn('test_id', $value);
                    } else {
                        $query->where('test_id', '=', $value);
                    }
                    break;
                case 'question_id':
                    if (is_array($value)) {
                        $query->whereIn('question_id', $value);
                    } else {
                        $query->where('question_id', '=', $value);
                    }
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'order':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }
            switch(strtolower($key)) {
                case 'id':
                case 'order':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
