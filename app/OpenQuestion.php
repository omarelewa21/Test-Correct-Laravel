<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;

class OpenQuestion extends Question implements QuestionInterface {

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
}
