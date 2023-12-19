<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;
use Illuminate\Support\Str;

class OpenQuestion extends Question implements QuestionInterface {

    use UuidTrait;

    protected $casts = [
        'uuid'                  => EfficientUuid::class,
        'spell_check_available' => 'boolean',
        'text_formatting'       => 'boolean',
        'mathml_functions'      => 'boolean',
        'restrict_word_amount'  => 'boolean',
        'max_words'             => 'integer',
        'deleted_at'            => 'datetime',
    ];

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
    protected $fillable = [
        'subtype',
        'answer',
        'spell_check_available',
        'text_formatting',
        'mathml_functions',
        'restrict_word_amount',
        'max_words',
    ];

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

    public function canCreateSystemRatingForAnswer($answer): bool
    {
        return false;
    }

    public function checkAnswer($answer)
    {
        return false;
    }
}
