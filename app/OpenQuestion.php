<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Scopes\RemoveUuidScope;
use tcCore\Traits\UuidTrait;

class OpenQuestion extends Question implements QuestionInterface {

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'spell_check_available' => 'boolean',
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
    protected $table = 'open_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subtype', 'answer', 'spell_check_available'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
//
//    protected static function booted()
//    {
//        static::addGlobalScope(new RemoveUuidScope);
//    }

    public function question() {

        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }



    public function loadRelated()
    {
        // Open questions do not have related stuff, so this does nothing!
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        $question->setAttribute('uuid', Uuid::uuid4());

        if ($question->save() === false) {
            return false;
        }

        return $question;
    }

    public function canCheckAnswer() {
        return false;
    }

    public function checkAnswer($answer) {
        return false;
    }

    public function getCaptionAttribute() {
        if ($this->subtype === 'writing') {
            return __('test_take.writing_assignment_question');
        }

        return parent::getCaptionAttribute();
    }
}
