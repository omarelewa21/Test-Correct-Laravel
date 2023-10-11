<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Lib\Question\QuestionInterface;

class RelationQuestion extends Question implements QuestionInterface
{
    protected $fillable = [
        'uuid',
    ];
    protected $table = 'relation_questions';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'uuid'       => EfficientUuid::class
    ];

    public static function boot(): void
    {
        parent::boot();
    }

    public function loadRelated()
    {
        // TODO: Implement loadRelated() method.
    }

    public function canCheckAnswer()
    {
        // TODO: Implement canCheckAnswer() method.
    }

    public function checkAnswer($answer)
    {
        // TODO: Implement checkAnswer() method.
    }
}
