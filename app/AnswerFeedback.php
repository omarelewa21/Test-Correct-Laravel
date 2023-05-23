<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use tcCore\Lib\CkEditorComments\CommentThread;
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
        'message',
        'thread_id',
        'comment_id'
    ];

    public function answer(){
        return $this->belongsTo(Answer::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * Get CommentThread objects for CKeditor5 Comments plugin
     *
     * @param $answerId
     * @return array
     */
    public static function getCommentThreadsByAnswerId($answerId) : array
    {
        $answerIds = Arr::wrap($answerId);

        return self::whereIn('answer_id', $answerIds)->get()->map(function ($answerFeedback) {
            return (array) CommentThread::getByModel($answerFeedback);
        })->toArray();
    }
}
