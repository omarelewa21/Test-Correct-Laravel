<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class InfoscreenQuestion extends Question implements QuestionInterface {

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','created_at','updated_at'];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'infoscreen_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subtype', 'answer'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


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
        return true;
    }

    public function checkAnswer($answer) {
        return 0;
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
