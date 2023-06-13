<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use tcCore\Http\Enums\Attributes\HexColor;
use tcCore\Http\Enums\CommentEmoji;
use tcCore\Http\Enums\CommentMarkerColor;
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

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get CommentThread objects for CKeditor5 Comments plugin
     *
     * @param $answerId
     * @return array
     */
    public static function getCommentsData($answerId)
    {
        $answerIds = Arr::wrap($answerId);

        return self::whereIn('answer_id', $answerIds)->with('user:id,uuid')
            ->where('comment_id', '<>', 'null')
            ->get()
            ->map(function ($answerFeedback) {
                return [
                    "threadId"   => $answerFeedback->thread_id,
                    "comments"   => [[
                                         "commentId"  => $answerFeedback->comment_id,
                                         "authorId"   => $answerFeedback->user->uuid,
                                         "content"    => $answerFeedback->message ?: '<p></p>', //ckeditor-comments doesn't allow empty comments
                                         "createdAt"  => $answerFeedback->created_at->format("Y-m-d H:i:s"),
                                         "attributes" => null,
                                     ]],
                    "context"    => null,
                    "resolvedAt" => null,
                    "resolvedBy" => null,
                    "attributes" => [],
                    "uuid"       => $answerFeedback->uuid,
                    "iconName"   => CommentEmoji::tryFrom($answerFeedback->comment_emoji)?->getIconComponentName()
                ];
            })->toArray();
    }

    public function getColor($opacity = 1)
    {

        if(!isset($this->comment_color)) {
            return CommentMarkerColor::BLUE->getRgbColorCode($opacity);
        }

        return CommentMarkerColor::tryFrom($this->comment_color)->getRgbColorCode($opacity);
    }
}
