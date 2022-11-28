<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class AnswerFeedback extends Model
{
    use SoftDeletes;
    use UuidTrait;

    protected $table = 'answers_feedback';

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'answer_id',
        'user_id',
        'message'
    ];

    public function answer(){
        return $this->belongsTo(Answer::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
