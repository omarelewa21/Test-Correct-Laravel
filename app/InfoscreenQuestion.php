<?php namespace tcCore;

use tcCore\Http\Traits\Questions\WithQuestionDuplicating;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Traits\UuidTrait;

class InfoscreenQuestion extends Question implements QuestionInterface {

    use UuidTrait;
    use WithQuestionDuplicating;


    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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


    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function loadRelated()
    {
        // Open questions do not have related stuff, so this does nothing!
    }

    public function duplicate(array $attributes, $ignore = null) {
        return $this->specificDuplication($attributes, $ignore);
    }

    public function canCreateSystemRatingForAnswer($answer): bool
    {
        return true;
    }

    public function checkAnswer($answer) {
        return 0;
    }

    public function getStudentPlayerComponent($context = 'question'): string
    {
        return str(parent::getStudentPlayerComponent($context))->replace('infoscreen', 'info-screen');
    }

}
