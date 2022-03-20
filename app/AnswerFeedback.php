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

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
        'answer_id',
        'sender_id',
        'message'
    ];

    public function answer(){
        return $this->belongsTo(Answer::class);
    }

    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }
}
